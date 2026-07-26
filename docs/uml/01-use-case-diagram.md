# Use Case Diagram - Sistem Pengajuan Surat Perizinan Kampus

## Aktor
- **Mahasiswa**: Pengguna utama yang mengajukan surat
- **Staff**: Petugas administrasi yang mereview pengajuan
- **Dekan**: Pimpinan fakultas yang menyetujui surat tertentu

## Use Case Diagram (Mermaid)

```mermaid
useCaseDiagram
    actor "Mahasiswa" as M
    actor "Staff" as S
    actor "Dekan" as D

    package "Sistem Pengajuan Surat" {
        usecase "Login" as UC1
        usecase "Lihat Dashboard" as UC2
        usecase "Ajukan Surat" as UC3
        usecase "Upload Lampiran" as UC4
        usecase "Lihat Status Pengajuan" as UC5
        usecase "Lihat Riwayat" as UC6
        usecase "Edit Pengajuan" as UC7
        usecase "Batal Pengajuan" as UC8
        
        usecase "Review Pengajuan" as UC9
        usecase "Approve Pengajuan" as UC10
        usecase "Reject Pengajuan" as UC11
        usecase "Lihat Semua Pengajuan" as UC12
        usecase "Filter & Search" as UC13
        
        usecase "Final Approval" as UC14
        usecase "Lihat Rekapitulasi" as UC15
        usecase "Generate Laporan" as UC16
    }

    M --> UC1
    M --> UC2
    M --> UC3
    M --> UC4
    M --> UC5
    M --> UC6
    M --> UC7
    M --> UC8

    S --> UC1
    S --> UC2
    S --> UC9
    S --> UC10
    S --> UC11
    S --> UC12
    S --> UC13

    D --> UC1
    D --> UC2
    D --> UC9
    D --> UC14
    D --> UC15
    D --> UC16

    UC3 ..> UC4 : <<include>>
    UC7 ..> UC5 : <<extend>>
    UC8 ..> UC5 : <<extend>>
    UC10 ..> UC9 : <<include>>
    UC11 ..> UC9 : <<include>>
    UC14 ..> UC9 : <<include>>
```

## Deskripsi Use Case

| Use Case | Aktor | Deskripsi |
|----------|-------|-----------|
| Login | Semua | Autentikasi pengguna ke sistem |
| Lihat Dashboard | Semua | Melihat ringkasan data dan statistik |
| Ajukan Surat | Mahasiswa | Membuat pengajuan surat baru |
| Upload Lampiran | Mahasiswa | Mengunggah dokumen pendukung (max 3 file) |
| Lihat Status Pengajuan | Semua | Melihat status pengajuan surat |
| Lihat Riwayat | Mahasiswa | Melihat riwayat pengajuan |
| Edit Pengajuan | Mahasiswa | Mengedit pengajuan yang masih pending |
| Batal Pengajuan | Mahasiswa | Membatalkan pengajuan yang masih pending |
| Review Pengajuan | Staff, Dekan | Meninjau pengajuan surat |
| Approve Pengajuan | Staff, Dekan | Menyetujui pengajuan surat |
| Reject Pengajuan | Staff, Dekan | Menolak pengajuan surat dengan alasan |
| Lihat Semua Pengajuan | Staff | Melihat semua pengajuan dari mahasiswa |
| Filter & Search | Staff | Mencari dan memfilter pengajuan |
| Final Approval | Dekan | Menyetujui akhir untuk surat tertentu |
| Lihat Rekapitulasi | Dekan | Melihat ringkasan statistik |
| Generate Laporan | Dekan | Membuat laporan bulanan |
