# Sequence Diagram - Alur Utama (Pengajuan Surat)

## Alur: Mahasiswa Mengajukan Surat

```mermaid
sequenceDiagram
    autonumber
    participant M as Mahasiswa
    participant F as Filament UI
    participant C as Controller
    participant S as LetterService
    participant DB as Database
    participant N as Notification

    M->>F: Akses halaman pengajuan
    F->>M: Tampilkan form pengajuan
    
    M->>F: Isi form + upload lampiran
    F->>C: Submit pengajuan
    
    C->>S: createSubmission(data, files)
    S->>DB: Insert letter_submission
    S->>DB: Insert attachments
    S->>DB: Insert activity_log
    
    alt Ada lampiran
        S->>S: uploadAttachment()
        S->>DB: Save file ke storage
    end
    
    S->>N: Kirim notifikasi ke Staff
    N->>DB: Insert notification
    
    S-->>C: Return submission
    C-->>F: Return response
    F-->>M: Tampilkan pesan sukses
    
    Note over M,N: Staff menerima notifikasi
```

## Deskripsi Alur

1. **Mahasiswa** mengakses halaman pengajuan surat
2. **Filament UI** menampilkan form pengajuan
3. **Mahasiswa** mengisi form dan mengunggah lampiran
4. **Controller** menerima data dan meneruskan ke **LetterService**
5. **LetterService** menyimpan pengajuan ke database
6. **LetterService** menyimpan lampiran jika ada
7. **LetterService** mencatat aktivitas ke activity log
8. **LetterService** mengirim notifikasi ke staff
9. **Mahasiswa** menerima pesan sukses
