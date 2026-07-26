# Activity Diagram - Alur Kerja End-to-End

## Activity Diagram (Mermaid)

```mermaid
flowchart TD
    Start([Mulai]) --> Login{Login}
    Login -->|Berhasil| RoleCheck{Cek Role}
    Login -->|Gagal| Login
    
    RoleCheck -->|Mahasiswa| M_Dashboard[Dashboard Mahasiswa]
    RoleCheck -->|Staff| S_Dashboard[Dashboard Staff]
    RoleCheck -->|Dekan| D_Dashboard[Dashboard Dekan]
    
    %% Alur Mahasiswa
    M_Dashboard --> M_Option{Pilihan Aksi}
    M_Option -->|Ajukan Surat| M_Form[Form Pengajuan]
    M_Option -->|Lihat Status| M_Status[Daftar Pengajuan]
    M_Option -->|Lihat Riwayat| M_History[Riwayat Pengajuan]
    
    M_Form --> M_Fill[Isi Form]
    M_Fill --> M_Upload[Upload Lampiran]
    M_Upload --> M_Submit[Submit Pengajuan]
    M_Submit --> M_Success[Pesan Sukses]
    M_Success --> M_Status
    
    M_Status --> M_Detail[Detail Pengajuan]
    M_Detail --> M_Edit{Bisa Edit?}
    M_Edit -->|Ya| M_Form
    M_Edit -->|Tidak| M_Status
    
    %% Alur Staff
    S_Dashboard --> S_Queue[Antrian Pengajuan]
    S_Queue --> S_Select[Pilih Pengajuan]
    S_Select --> S_Detail[Detail Pengajuan]
    S_Detail --> S_Review{Keputusan}
    
    S_Review -->|Approve| S_CheckDekan{Butuh Dekan?}
    S_Review -->|Reject| S_Reject[Form Penolakan]
    
    S_CheckDekan -->|Ya| S_Status[Status: staff_reviewed]
    S_CheckDekan -->|Tidak| S_Approve[Status: approved]
    
    S_Reject --> S_ConfirmReject[Konfirmasi Tolak]
    S_ConfirmReject --> S_Rejected[Status: rejected]
    
    S_Status --> S_NotifyDekan[Notifikasi ke Dekan]
    S_Approve --> S_NotifyM[Notifikasi ke Mahasiswa]
    S_Rejected --> S_NotifyM
    
    %% Alur Dekan
    D_Dashboard --> D_Queue[Antrian Persetujuan]
    D_Queue --> D_Select[Pilih Pengajuan]
    D_Select --> D_Detail[Detail Pengajuan]
    D_Detail --> D_Review{Keputusan}
    
    D_Review -->|Approve| D_Approve[Status: approved]
    D_Review -->|Reject| D_Reject[Form Penolakan]
    
    D_Approve --> D_NotifyM[Notifikasi ke Mahasiswa]
    D_Reject --> D_ConfirmReject[Konfirmasi Tolak]
    D_ConfirmReject --> D_Rejected[Status: rejected]
    D_Rejected --> D_NotifyM
    
    %% Background Jobs
    CronJob[Cron Job: Auto Reject] --> CronCheck{Cek Deadline}
    CronCheck -->|Melebihi Batas| CronReject[Auto Reject]
    CronCheck -->|Masih Waktu| CronSkip[Skip]
    
    CronReject --> CronNotify[Notifikasi ke Mahasiswa]
    
    %% End States
    M_History --> End([Selesai])
    M_Detail --> End
    S_NotifyM --> End
    D_NotifyM --> End
    CronSkip --> End
    CronNotify --> End
    
    style Start fill:#4CAF50,color:#fff
    style End fill:#f44336,color:#fff
    style Login fill:#2196F3,color:#fff
    style RoleCheck fill:#FF9800,color:#fff
    style M_Form fill:#9C27B0,color:#fff
    style S_Review fill:#f44336,color:#fff
    style D_Review fill:#f44336,color:#fff
    style CronJob fill:#607D8B,color:#fff
```

## Deskripsi Alur

### Alur Utama
1. **Login**: Pengguna melakukan autentikasi
2. **Role Check**: Sistem menentukan role dan mengarahkan ke dashboard
3. **Dashboard**: Menampilkan data sesuai role

### Alur Mahasiswa
1. Mengisi form pengajuan
2. Mengunggah lampiran (opsional, max 3 file)
3. Submit pengajuan
4. Melihat status dan riwayat
5. Edit pengajuan (jika masih pending)

### Alur Staff
1. Melihat antrian pengajuan
2. Review detail pengajuan
3. Approve atau Reject
4. Jika approve &requires dekan → notifikasi ke dekan
5. Jika approve &tidak requires dekan → langsung approved
6. Jika reject → masukkan alasan penolakan

### Alur Dekan
1. Melihat antrian persetujuan
2. Review detail pengajuan
3. Approve atau Reject
4. Notifikasi ke mahasiswa

### Background Jobs
1. **Cron Job**: Auto reject pengajuan melebihi deadline
2. **Queue Job**: Kirim notifikasi email async
3. **Queue Job**: Generate PDF surat
