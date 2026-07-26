# Sequence Diagram - Alur Kedua (Proses Persetujuan)

## Alur: Staff/Dekan Review dan Approve/Reject

```mermaid
sequenceDiagram
    autonumber
    participant S as Staff/Dekan
    participant F as Filament UI
    participant C as Controller
    participant LS as LetterService
    participant DB as Database
    participant N as Notification
    participant M as Mahasiswa

    S->>F: Aksi review (Approve/Reject)
    F->>C: Submit review action
    
    alt Staff Review
        C->>LS: staffReview(submission, staff, approved, reason)
    else Dekan Review
        C->>LS: dekanReview(submission, dekan, approved, reason)
    end
    
    LS->>DB: Update letter_submission status
    LS->>DB: Insert activity_log
    
    alt Approved & Requires Dekan
        LS->>N: Notify Dekan for approval
        N->>DB: Insert notification
    else Approved & No Dekan Required
        LS->>N: Notify Mahasiswa (Approved)
        N->>DB: Insert notification
        N->>M: Email/SMS notification
    else Rejected
        LS->>N: Notify Mahasiswa (Rejected)
        N->>DB: Insert notification
        N->>M: Email/SMS notification
    end
    
    LS-->>C: Return updated submission
    C-->>F: Return response
    F-->>S: Tampilkan status terbaru
```

## Deskripsi Alur

### Alur Review oleh Staff
1. **Staff** memilih pengajuan untuk direview
2. **Filament UI** menampilkan detail pengajuan
3. **Staff** memilih approve atau reject
4. **Controller** menerima aksi dan meneruskan ke **LetterService**
5. **LetterService** memanggil `staffReview()`:
   - Jika approve &requires dekan → status = `staff_reviewed`, notifikasi ke dekan
   - Jika approve &tidak requires dekan → status = `approved`, notifikasi ke mahasiswa
   - Jika reject → status = `rejected`, notifikasi ke mahasiswa

### Alur Review oleh Dekan
1. **Dekan** melihat pengajuan yang perlu persetujuan
2. **Dekan** memilih approve atau reject
3. **LetterService** memanggil `dekanReview()`:
   - Jika approve → status = `approved`, notifikasi ke mahasiswa
   - Jika reject → status = `rejected`, notifikasi ke mahasiswa

### Status Transitions
```
pending → staff_reviewed (Staff approve, requires dekan)
pending → approved (Staff approve, tidak requires dekan)
pending → rejected (Staff reject)
staff_reviewed → approved (Dekan approve)
staff_reviewed → rejected (Dekan reject)
```
