<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Zirve Portal kullanici yonetim servisi.
 * Kullanici CRUD, rol yonetimi, yetkilendirme, sube islemleri ve impersonate ozellikleri.
 */
class UserService extends BaseService
{
    // ─── Kullanici Bilgileri ──────────────────────────────────────────

    /**
     * Oturum acmis kullanicinin bilgilerini getirir.
     *
     * @return array Kullanici profil bilgileri
     */
    public function me(): array
    {
        return $this->http->get('/user/me');
    }

    /**
     * Belirtilen kullanicinin tam bilgilerini getirir.
     *
     * @param int $id Kullanici ID
     * @return array Kullanicinin tum detaylari
     */
    public function getComplete(int $id): array
    {
        return $this->http->get("/user/fetchCompleteUser/{$id}");
    }

    /**
     * Kullanici listesini sayfalanmis olarak getirir.
     *
     * @param int $page Sayfa numarasi (0 tabanli)
     * @param int $size Sayfa basi kayit sayisi
     * @return array Sayfalanmis kullanici listesi
     */
    public function list(int $page = 0, int $size = 10): array
    {
        return $this->http->get('/user/fetchUsers', [
            'page' => $page,
            'size' => $size,
        ]);
    }

    /**
     * Kayit numarasina gore kullanici getirir.
     *
     * @param int $id Kayit numarasi (recordId)
     * @return array Kullanici bilgileri
     */
    public function getByRecordId(int $id): array
    {
        return $this->http->get('/user/fetchUserByRecordId', [
            'recordId' => $id,
        ]);
    }

    /**
     * Kullanici adina gore kullanici arar.
     *
     * @param string $username Kullanici adi
     * @return array Kullanici bilgileri
     */
    public function findByUsername(string $username): array
    {
        return $this->http->get('/user/findByUsername', [
            'username' => $username,
        ]);
    }

    /**
     * Tum kullanici adi ve kayit numarasi ciftlerini getirir.
     *
     * @return array Kullanici adi ve recordId listesi
     */
    public function findAllUsernameAndRecordId(): array
    {
        return $this->http->get('/user/findAllUsernameAndRecordId');
    }

    /**
     * Oturum acmis kullanicinin kullanici adini getirir.
     *
     * @return array Kullanici adi bilgisi
     */
    public function getUsername(): array
    {
        return $this->http->get('/user/getUsername');
    }

    /**
     * Atanabilir kullanici (assignee) bilgisini getirir.
     *
     * @return array Assignee bilgileri
     */
    public function getAssignee(): array
    {
        return $this->http->get('/user/getAssignee');
    }

    // ─── Kullanici CRUD ───────────────────────────────────────────────

    /**
     * Yeni kullanici olusturur.
     *
     * @param array $data Kullanici bilgileri
     * @return array Olusturulan kullanici bilgileri
     */
    public function create(array $data): array
    {
        return $this->http->post('/user/createUser', $data);
    }

    /**
     * Kullanici profilini gunceller.
     *
     * @param array $data Guncellenecek profil alanlari
     * @return array Guncelleme sonucu
     */
    public function updateProfile(array $data): array
    {
        return $this->http->post('/user/updateProfile', $data);
    }

    /**
     * Kullanici sifresini gunceller.
     *
     * @param array $data currentPassword, newPassword, newPasswordCopy alanlari
     * @return array Sifre guncelleme sonucu
     */
    public function updatePassword(array $data): array
    {
        return $this->http->post('/user/updatePassword', $data);
    }

    /**
     * Kullaniciyi siler.
     *
     * @param array $data Silinecek kullanici bilgileri
     * @return array Silme islemi sonucu
     */
    public function delete(array $data): array
    {
        return $this->http->post('/user/deleteUser', $data);
    }

    /**
     * Sifremi unuttum islemi baslatir.
     *
     * @param array $data E-posta veya kullanici adi bilgileri
     * @return array Islem sonucu
     */
    public function forgotPassword(array $data): array
    {
        return $this->http->post('/user/forgotPassword', $data);
    }

    /**
     * Kullanicinin kimlik sifresini kontrol eder.
     *
     * @param int $id Kullanici ID
     * @return array Dogrulama sonucu
     */
    public function checkIdentityPassword(int $id): array
    {
        return $this->http->post("/user/checkUsersIdentityPassword/{$id}");
    }

    // ─── Rol Yonetimi ─────────────────────────────────────────────────

    /**
     * Tum rolleri getirir.
     *
     * @return array Rol listesi
     */
    public function getRoles(): array
    {
        return $this->http->get('/user/fetchRoles');
    }

    /**
     * Belirtilen rolu getirir.
     *
     * @param int $id Rol ID
     * @return array Rol detaylari
     */
    public function getRole(int $id): array
    {
        return $this->http->get("/user/fetchRoles/{$id}");
    }

    /**
     * Musteri rollerini getirir.
     *
     * @return array Musteri rol listesi
     */
    public function fetchCustomerRoles(): array
    {
        return $this->http->get('/user/fetchCustomerRoles');
    }

    /**
     * Yeni rol olusturur.
     *
     * @param array $data Rol bilgileri
     * @return array Olusturulan rol bilgileri
     */
    public function createRole(array $data): array
    {
        return $this->http->post('/user/createRole', $data);
    }

    /**
     * Belirtilen rolu gunceller.
     *
     * @param int   $id   Rol ID
     * @param array $data Guncellenecek rol alanlari
     * @return array Guncelleme sonucu
     */
    public function updateRole(int $id, array $data): array
    {
        return $this->http->post("/user/updateRole/{$id}", $data);
    }

    /**
     * Belirtilen rolu siler.
     *
     * @param int $id Rol ID
     * @return array Silme islemi sonucu
     */
    public function removeRole(int $id): array
    {
        return $this->http->delete("/user/removeRole/{$id}");
    }

    /**
     * Secilen role gore kullanicilari getirir.
     *
     * @param array $query Sorgu parametreleri
     * @return array Kullanici listesi
     */
    public function getUserBySelectedRole(array $query): array
    {
        return $this->http->get('/user/getUserBySelectedRole', $query);
    }

    // ─── Yetkilendirme ────────────────────────────────────────────────

    /**
     * Kullanicinin yetkilerini getirir.
     *
     * @param int $id Kullanici ID
     * @return array Yetki listesi
     */
    public function getUserRights(int $id): array
    {
        return $this->http->get("/user/fetchUserRights/{$id}");
    }

    /**
     * Kullanicinin yetkilerini gunceller.
     *
     * @param int   $id   Kullanici ID
     * @param array $data Guncellenecek yetki bilgileri
     * @return array Guncelleme sonucu
     */
    public function updateUserRights(int $id, array $data): array
    {
        return $this->http->post("/user/updateUserRights/{$id}", $data);
    }

    // ─── Sube Islemleri ───────────────────────────────────────────────

    /**
     * Kullanicinin okuyabildigi subeleri getirir.
     *
     * @return array Okunabilir sube listesi
     */
    public function getReadableBranches(): array
    {
        return $this->http->get('/user/fetchReadableUserBranches');
    }

    /**
     * Ekleme icin musteri subelerini getirir.
     *
     * @return array Sube listesi
     */
    public function fetchBranchesForAdding(): array
    {
        return $this->http->get('/user/fetchCustomerBranchesForAdding');
    }

    /**
     * C tipi musteri subelerini sayfalanmis olarak getirir.
     *
     * @param int $page Sayfa numarasi (0 tabanli)
     * @param int $size Sayfa basi kayit sayisi
     * @return array Sayfalanmis sube listesi
     */
    public function fetchBranchesForC(int $page = 0, int $size = 10): array
    {
        return $this->http->get('/user/fetchCustomerBranchesForC', [
            'page' => $page,
            'size' => $size,
        ]);
    }

    /**
     * C tipi musteri subelerini duzenleme icin sayfalanmis olarak getirir.
     *
     * @param int $page Sayfa numarasi (0 tabanli)
     * @param int $size Sayfa basi kayit sayisi
     * @return array Sayfalanmis sube listesi
     */
    public function fetchBranchesForCEdit(int $page = 0, int $size = 10): array
    {
        return $this->http->get('/user/fetchCustomerBranchesForCEdit', [
            'page' => $page,
            'size' => $size,
        ]);
    }

    // ─── Musteri & Alias ──────────────────────────────────────────────

    /**
     * Iliskili musteriyi getirir.
     *
     * @return array Musteri bilgileri
     */
    public function getRelatedCustomer(): array
    {
        return $this->http->get('/user/fetchRelatedCustomer');
    }

    /**
     * Alias (takma ad) listesini getirir.
     *
     * @return array Alias listesi
     */
    public function getAliasList(): array
    {
        return $this->http->get('/user/getAliasList');
    }

    /**
     * Musteriye gore alias listesini getirir.
     *
     * @return array Musteriye ait alias listesi
     */
    public function getAliasListByCustomer(): array
    {
        return $this->http->get('/user/getAliasListByCustomer');
    }

    // ─── Impersonate (Kullanici Taklit) ───────────────────────────────

    /**
     * Baska bir kullanici olarak islem yapmaya baslar.
     *
     * @param array $data Taklit edilecek kullanici bilgileri
     * @return array Impersonate sonucu
     */
    public function impersonate(array $data): array
    {
        return $this->http->post('/user/impersonate', $data);
    }

    /**
     * Kullanici taklit modundan cikar.
     *
     * @return array Cikis sonucu
     */
    public function exitImpersonate(): array
    {
        return $this->http->post('/user/exitImpersonate');
    }

    /**
     * Taklit edilen kullanicinin musteri ID'sini getirir.
     *
     * @return array Musteri ID bilgisi
     */
    public function impersonateCustomerId(): array
    {
        return $this->http->get('/user/impersonateCustomerId');
    }

    /**
     * Taklit edilen kullanicinin kaynak ID'sini getirir.
     *
     * @return array Kaynak ID bilgisi
     */
    public function impersonateSourceId(): array
    {
        return $this->http->get('/user/impersonateSourceId');
    }

    // ─── Toplu Kullanicilar ───────────────────────────────────────────

    /**
     * Toplu kullanicilari sayfalanmis olarak getirir.
     *
     * @param int $page Sayfa numarasi (0 tabanli)
     * @param int $size Sayfa basi kayit sayisi
     * @return array Sayfalanmis toplu kullanici listesi
     */
    public function fetchCollectiveUsers(int $page = 0, int $size = 10): array
    {
        return $this->http->get('/user/fetchCollectiveUsers', [
            'page' => $page,
            'size' => $size,
        ]);
    }

    // ─── E-posta & Bildirim ───────────────────────────────────────────

    /**
     * Yeni e-posta gonderir.
     *
     * @param array $data E-posta icerigi ve alici bilgileri
     * @return array Gonderim sonucu
     */
    public function sendNewMail(array $data): array
    {
        return $this->http->post('/user/sendNewMail', $data);
    }

    // ─── Disa Aktarim & Raporlar ──────────────────────────────────────

    /**
     * Kullanici listesini Excel dosyasi olarak indirir.
     *
     * @return string Excel dosyasi icerigi (binary)
     */
    public function downloadExcel(): string
    {
        return $this->http->download('/user/downloadExcelFile');
    }

    /**
     * Indirme isteklerini sayfalanmis olarak getirir.
     *
     * @param int $page Sayfa numarasi (0 tabanli)
     * @param int $size Sayfa basi kayit sayisi
     * @return array Sayfalanmis indirme istekleri listesi
     */
    public function downloadRequests(int $page = 0, int $size = 10): array
    {
        return $this->http->get('/user/downloadRequests', [
            'page' => $page,
            'size' => $size,
        ]);
    }

    // ─── Diger Islemler ───────────────────────────────────────────────

    /**
     * Stok muhasebe durumunu gunceller.
     *
     * @param array $data Stok muhasebe ayarlari
     * @return array Guncelleme sonucu
     */
    public function updateIsStockAccounting(array $data): array
    {
        return $this->http->post('/user/updateIsStockAccounting', $data);
    }

    /**
     * Eczaci kart anket durumunu kontrol eder.
     *
     * @return array Anket durumu
     */
    public function checkEczaciKartSurvey(): array
    {
        return $this->http->get('/user/checkEczaciKartSurvey');
    }
}
