<?php

require_once __DIR__ . '/../models/Barang.php';
require_once __DIR__ . '/../models/KonfigurasiNota.php';
require_once __DIR__ . '/../helpers/format.php';

class SettingController {
    private $barangModel;
    private $konfigurasiNota;

    public function __construct() {
        $this->barangModel = new Barang();
        $this->konfigurasiNota = new KonfigurasiNota();
    }

    public function kategoriSatuan() {
        $kategori = $this->barangModel->getAllKategori();
        $satuan = $this->barangModel->getAllSatuan();
        require_once __DIR__ . '/../views/setting/kategori-satuan.php';
    }

    public function addKategori() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_kategori = $_POST['nama_kategori'] ?? '';

            if (empty($nama_kategori)) {
                $_SESSION['error'] = 'Nama kategori tidak boleh kosong';
                redirect('/setting/kategori-satuan');
            }

            if ($this->barangModel->addKategori($nama_kategori)) {
                $_SESSION['success'] = 'Kategori berhasil ditambahkan';
            } else {
                $_SESSION['error'] = 'Kategori sudah ada atau gagal ditambahkan';
            }
            redirect('/setting/kategori-satuan');
        }
    }

    public function addSatuan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_satuan = $_POST['nama_satuan'] ?? '';

            if (empty($nama_satuan)) {
                $_SESSION['error'] = 'Nama satuan tidak boleh kosong';
                redirect('/setting/kategori-satuan');
            }

            if ($this->barangModel->addSatuan($nama_satuan)) {
                $_SESSION['success'] = 'Satuan berhasil ditambahkan';
            } else {
                $_SESSION['error'] = 'Satuan sudah ada atau gagal ditambahkan';
            }
            redirect('/setting/kategori-satuan');
        }
    }

    public function updateKategori() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_kategori'] ?? null;
            $nama = trim($_POST['nama_kategori'] ?? '');

            if (!$id || $nama === '') {
                $_SESSION['error'] = 'Nama kategori tidak boleh kosong';
                redirect('/setting/kategori-satuan');
            }

            if ($this->barangModel->updateKategori($id, $nama)) {
                $_SESSION['success'] = 'Kategori berhasil diperbarui';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui kategori';
            }
            redirect('/setting/kategori-satuan');
        }
    }

    public function deleteKategori() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_kategori'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Kategori tidak ditemukan';
                redirect('/setting/kategori-satuan');
            }

            if ($this->barangModel->deleteKategori($id)) {
                $_SESSION['success'] = 'Kategori berhasil dihapus';
            } else {
                $_SESSION['error'] = 'Gagal menghapus kategori. Pastikan tidak dipakai oleh barang.';
            }
            redirect('/setting/kategori-satuan');
        }
    }

    public function updateSatuan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_satuan'] ?? null;
            $nama = trim($_POST['nama_satuan'] ?? '');

            if (!$id || $nama === '') {
                $_SESSION['error'] = 'Nama satuan tidak boleh kosong';
                redirect('/setting/kategori-satuan');
            }

            if ($this->barangModel->updateSatuan($id, $nama)) {
                $_SESSION['success'] = 'Satuan berhasil diperbarui';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui satuan';
            }
            redirect('/setting/kategori-satuan');
        }
    }

    public function deleteSatuan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_satuan'] ?? null;
            if (!$id) {
                $_SESSION['error'] = 'Satuan tidak ditemukan';
                redirect('/setting/kategori-satuan');
            }

            if ($this->barangModel->deleteSatuan($id)) {
                $_SESSION['success'] = 'Satuan berhasil dihapus';
            } else {
                $_SESSION['error'] = 'Gagal menghapus satuan. Pastikan tidak dipakai oleh barang.';
            }
            redirect('/setting/kategori-satuan');
        }
    }

    public function nota() {
        $config = $this->konfigurasiNota->getConfig();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_toko' => $_POST['nama_toko'] ?? 'UD. BERSAUDARA',
                'alamat_toko' => $_POST['alamat_toko'] ?? '',
                'nomor_telepon' => $_POST['nomor_telepon'] ?? '',
                'email_toko' => $_POST['email_toko'] ?? '',
                'footer_nota' => $_POST['footer_nota'] ?? '',
                'lebar_kertas' => (int)($_POST['lebar_kertas'] ?? 80),
                'font_nota' => $_POST['font_nota'] ?? 'Arial',
                'tampilkan_jam' => isset($_POST['tampilkan_jam']) ? 1 : 0,
                'tampilkan_kode_barang' => isset($_POST['tampilkan_kode_barang']) ? 1 : 0,
                'tampilkan_satuan' => isset($_POST['tampilkan_satuan']) ? 1 : 0,
                'jumlah_diskon_terpisah' => isset($_POST['jumlah_diskon_terpisah']) ? 1 : 0,
                'custom_header_text' => $_POST['custom_header_text'] ?? '',
                'custom_footer_text' => $_POST['custom_footer_text'] ?? '',
                'tampilkan_nama_pembeli' => isset($_POST['tampilkan_nama_pembeli']) ? 1 : 0,
                'tampilkan_info_hutang' => isset($_POST['tampilkan_info_hutang']) ? 1 : 0,
            ];

            if ($this->konfigurasiNota->updateConfig($data)) {
                $_SESSION['success'] = 'Konfigurasi nota berhasil diperbarui!';
                $config = $this->konfigurasiNota->getConfig();
            } else {
                $_SESSION['error'] = 'Gagal memperbarui konfigurasi nota!';
            }
        }

        require_once __DIR__ . '/../views/setting/nota.php';
    }

    public function rolePermissions() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roles = PermissionGate::getConfigurableRoles();
            $submitted = $_POST['permissions'] ?? [];
            $allSaved = true;

            foreach ($roles as $role) {
                $permissions = [];
                if (isset($submitted[$role]) && is_array($submitted[$role])) {
                    $permissions = array_values(array_filter(array_map('strval', $submitted[$role])));
                }
                $saved = PermissionGate::saveRolePermissions($role, $permissions);
                if (!$saved) {
                    $allSaved = false;
                }
            }

            if ($allSaved) {
                $_SESSION['success'] = 'Hak akses role berhasil diperbarui.';
            } else {
                $_SESSION['error'] = 'Sebagian hak akses gagal diperbarui.';
            }

            redirect('/setting/role-permissions');
        }

        $roles = PermissionGate::getConfigurableRoles();
        $catalog = PermissionGate::getPermissionCatalog();
        $presetMatrix = PermissionGate::getRolePresetMatrix();
        $permissionTemplates = PermissionGate::getPermissionTemplates();
        $currentPermissions = [];
        foreach ($roles as $role) {
            $currentPermissions[$role] = PermissionGate::getRolePermissions($role);
        }

        require_once __DIR__ . '/../views/setting/role-permissions.php';
    }
}
