<?php

require_once __DIR__ . '/../config/database.php';

class InventoryBatch {
    private $conn;

    public function __construct(?PDO $conn = null) {
        if ($conn instanceof PDO) {
            $this->conn = $conn;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
        $this->ensureTables();
    }

    private function ensureTables(): void {
        try {
            $this->conn->exec("CREATE TABLE IF NOT EXISTS inventory_batches (
                id_batch SERIAL PRIMARY KEY,
                id_pembelian INT NOT NULL REFERENCES pembelian(id_pembelian) ON DELETE CASCADE,
                id_detail_pembelian INT NOT NULL REFERENCES detail_pembelian(id_detail) ON DELETE CASCADE,
                id_barang INT NOT NULL REFERENCES barang(id_barang) ON DELETE CASCADE,
                tanggal_batch TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                qty_awal NUMERIC(12,2) NOT NULL,
                qty_sisa NUMERIC(12,2) NOT NULL,
                harga_modal NUMERIC(12,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $this->conn->exec("CREATE TABLE IF NOT EXISTS penjualan_batch_konsumsi (
                id_konsumsi SERIAL PRIMARY KEY,
                id_penjualan INT NOT NULL REFERENCES penjualan(id_penjualan) ON DELETE CASCADE,
                id_detail_penjualan INT NOT NULL REFERENCES detail_penjualan(id_detail) ON DELETE CASCADE,
                id_batch INT NULL REFERENCES inventory_batches(id_batch) ON DELETE SET NULL,
                id_barang INT NOT NULL REFERENCES barang(id_barang) ON DELETE CASCADE,
                qty NUMERIC(12,2) NOT NULL,
                harga_modal NUMERIC(12,2) NOT NULL,
                total_modal NUMERIC(12,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_inventory_batches_barang_sisa ON inventory_batches(id_barang, qty_sisa, tanggal_batch)");
            $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_konsumsi_penjualan ON penjualan_batch_konsumsi(id_penjualan)");
            $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_konsumsi_batch ON penjualan_batch_konsumsi(id_batch)");
        } catch (Exception $e) {
            error_log('ensure inventory batch tables error: ' . $e->getMessage());
        }
    }

    public function createBatchFromPurchaseDetail(int $idPembelian, int $idDetailPembelian, int $idBarang, float $qty, float $hargaModal, ?string $tanggalBatch = null): bool {
        $tanggal = $tanggalBatch ?: date('Y-m-d H:i:s');
        $query = "INSERT INTO inventory_batches
                  (id_pembelian, id_detail_pembelian, id_barang, tanggal_batch, qty_awal, qty_sisa, harga_modal)
                  VALUES (:id_pembelian, :id_detail_pembelian, :id_barang, :tanggal_batch, :qty_awal, :qty_sisa, :harga_modal)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id_pembelian', $idPembelian, PDO::PARAM_INT);
        $stmt->bindValue(':id_detail_pembelian', $idDetailPembelian, PDO::PARAM_INT);
        $stmt->bindValue(':id_barang', $idBarang, PDO::PARAM_INT);
        $stmt->bindValue(':tanggal_batch', $tanggal);
        $stmt->bindValue(':qty_awal', $qty);
        $stmt->bindValue(':qty_sisa', $qty);
        $stmt->bindValue(':harga_modal', $hargaModal);
        return $stmt->execute();
    }

    public function consumeForSaleDetail(int $idPenjualan, int $idDetailPenjualan, int $idBarang, float $qtyJual, float $fallbackHargaModal = 0): array {
        $sisa = $qtyJual;
        $totalModal = 0.0;

        $queryBatch = "SELECT id_batch, qty_sisa, harga_modal
                       FROM inventory_batches
                       WHERE id_barang = :id_barang AND qty_sisa > 0
                       ORDER BY tanggal_batch ASC, id_batch ASC";
        $stmtBatch = $this->conn->prepare($queryBatch);
        $stmtBatch->bindValue(':id_barang', $idBarang, PDO::PARAM_INT);
        $stmtBatch->execute();
        $batches = $stmtBatch->fetchAll();

        foreach ($batches as $batch) {
            if ($sisa <= 0) {
                break;
            }

            $qtySisaBatch = (float)$batch['qty_sisa'];
            if ($qtySisaBatch <= 0) {
                continue;
            }

            $pakai = min($sisa, $qtySisaBatch);
            $hargaModal = (float)$batch['harga_modal'];
            $total = $pakai * $hargaModal;

            $updateBatch = $this->conn->prepare("UPDATE inventory_batches SET qty_sisa = qty_sisa - :qty WHERE id_batch = :id_batch");
            $updateBatch->bindValue(':qty', $pakai);
            $updateBatch->bindValue(':id_batch', $batch['id_batch'], PDO::PARAM_INT);
            $updateBatch->execute();

            $this->insertConsumption($idPenjualan, $idDetailPenjualan, (int)$batch['id_batch'], $idBarang, $pakai, $hargaModal, $total);

            $sisa -= $pakai;
            $totalModal += $total;
        }

        if ($sisa > 0) {
            $totalFallback = $sisa * $fallbackHargaModal;
            $this->insertConsumption($idPenjualan, $idDetailPenjualan, null, $idBarang, $sisa, $fallbackHargaModal, $totalFallback);
            $totalModal += $totalFallback;
        }

        $unitCost = $qtyJual > 0 ? ($totalModal / $qtyJual) : 0;
        return ['total_modal' => $totalModal, 'unit_modal' => $unitCost];
    }

    private function insertConsumption(int $idPenjualan, int $idDetailPenjualan, ?int $idBatch, int $idBarang, float $qty, float $hargaModal, float $total): void {
        $query = "INSERT INTO penjualan_batch_konsumsi
                  (id_penjualan, id_detail_penjualan, id_batch, id_barang, qty, harga_modal, total_modal)
                  VALUES (:id_penjualan, :id_detail_penjualan, :id_batch, :id_barang, :qty, :harga_modal, :total_modal)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id_penjualan', $idPenjualan, PDO::PARAM_INT);
        $stmt->bindValue(':id_detail_penjualan', $idDetailPenjualan, PDO::PARAM_INT);
        $stmt->bindValue(':id_batch', $idBatch, $idBatch === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':id_barang', $idBarang, PDO::PARAM_INT);
        $stmt->bindValue(':qty', $qty);
        $stmt->bindValue(':harga_modal', $hargaModal);
        $stmt->bindValue(':total_modal', $total);
        $stmt->execute();
    }

    public function rollbackSale(int $idPenjualan): void {
        $query = "SELECT id_batch, qty FROM penjualan_batch_konsumsi WHERE id_penjualan = :id_penjualan";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id_penjualan', $idPenjualan, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            if (!empty($row['id_batch'])) {
                $restore = $this->conn->prepare("UPDATE inventory_batches SET qty_sisa = qty_sisa + :qty WHERE id_batch = :id_batch");
                $restore->bindValue(':qty', (float)$row['qty']);
                $restore->bindValue(':id_batch', (int)$row['id_batch'], PDO::PARAM_INT);
                $restore->execute();
            }
        }

        $delete = $this->conn->prepare("DELETE FROM penjualan_batch_konsumsi WHERE id_penjualan = :id_penjualan");
        $delete->bindValue(':id_penjualan', $idPenjualan, PDO::PARAM_INT);
        $delete->execute();
    }

    public function canModifyPurchase(int $idPembelian): bool {
        $query = "SELECT COUNT(*) AS total
                  FROM penjualan_batch_konsumsi k
                  JOIN inventory_batches b ON b.id_batch = k.id_batch
                  WHERE b.id_pembelian = :id_pembelian";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id_pembelian', $idPembelian, PDO::PARAM_INT);
        $stmt->execute();
        $total = (int)($stmt->fetch()['total'] ?? 0);
        return $total === 0;
    }

    public function replacePurchaseBatches(int $idPembelian, array $details, ?string $tanggalBatch = null): bool {
        if (!$this->canModifyPurchase($idPembelian)) {
            return false;
        }

        $delete = $this->conn->prepare("DELETE FROM inventory_batches WHERE id_pembelian = :id_pembelian");
        $delete->bindValue(':id_pembelian', $idPembelian, PDO::PARAM_INT);
        $delete->execute();

        foreach ($details as $detail) {
            $this->createBatchFromPurchaseDetail(
                $idPembelian,
                (int)$detail['id_detail'],
                (int)$detail['id_barang'],
                (float)$detail['jumlah'],
                (float)$detail['harga_satuan'],
                $tanggalBatch
            );
        }
        return true;
    }
}
