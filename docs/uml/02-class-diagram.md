# Class Diagram - Sistem Pengajuan Surat Perizinan Kampus

## Class Diagram (Mermaid)

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string password
        +string role
        +string nim
        +string nip
        +string phone
        +string department
        +boolean is_active
        +datetime email_verified_at
        +datetime created_at
        +datetime updated_at
        +isMahasiswa() boolean
        +isStaff() boolean
        +isDekan() boolean
        +canApprove() boolean
    }

    class LetterType {
        +int id
        +string name
        +text description
        +boolean requires_dekan_approval
        +int max_days_to_review
        +boolean is_active
        +datetime created_at
        +datetime updated_at
        +scopeActive() Builder
        +scopeRequiresDekanApproval() Builder
    }

    class LetterSubmission {
        +int id
        +int user_id
        +int letter_type_id
        +string subject
        +text body
        +string status
        +text rejection_reason
        +int approved_by
        +int dekan_approved_by
        +date due_date
        +datetime created_at
        +datetime updated_at
        +getStatusLabel() string
        +getStatusColor() string
        +isPending() boolean
        +isApproved() boolean
        +isRejected() boolean
        +canBeApprovedByStaff() boolean
        +canBeApprovedByDekan() boolean
        +requiresDekanApproval() boolean
        +isOverdue() boolean
    }

    class Attachment {
        +int id
        +int letter_submission_id
        +string filename
        +string original_name
        +string mime_type
        +bigint size
        +string path
        +datetime created_at
        +datetime updated_at
        +getUrl() string
        +getDownloadUrl() string
        +getFormattedSize() string
        +isImage() boolean
        +isPdf() boolean
        +deleteFile() boolean
    }

    class ActivityLog {
        +int id
        +int letter_submission_id
        +int user_id
        +string action
        +text description
        +json old_values
        +json new_values
        +datetime created_at
        +datetime updated_at
        +getFormattedAction() string
    }

    class LetterService {
        +createSubmission() LetterSubmission
        +uploadAttachment() Attachment
        +staffReview() LetterSubmission
        +dekanReview() LetterSubmission
        +autoRejectOverdue() int
        -logActivity() ActivityLog
        -notifyStaff() void
        -notifyDekan() void
        -notifyUser() void
    }

    class LetterSubmissionPolicy {
        +viewAny() boolean
        +view() boolean
        +create() boolean
        +update() boolean
        +delete() boolean
        +approve() boolean
        +reject() boolean
        +downloadAttachment() boolean
    }

    User "1" --> "*" LetterSubmission : has
    User "1" --> "*" ActivityLog : creates
    User "1" --> "*" Attachment : uploads
    
    LetterType "1" --> "*" LetterSubmission : defines
    
    LetterSubmission "1" --> "*" Attachment : contains
    LetterSubmission "1" --> "*" ActivityLog : logs
    LetterSubmission "*" --> "1" User : submitted_by
    LetterSubmission "*" --> "0..1" User : approved_by
    LetterSubmission "*" --> "0..1" User : dekan_approved_by
    
    LetterService ..> LetterSubmission : manages
    LetterService ..> Attachment : manages
    LetterService ..> ActivityLog : creates
    LetterSubmissionPolicy ..> LetterSubmission : controls
```

## Deskripsi Class

| Class | Deskripsi | Relasi |
|-------|-----------|--------|
| User | Pengguna sistem (Mahasiswa, Staff, Dekan) | Memiliki banyak LetterSubmission, ActivityLog, Attachment |
| LetterType | Jenis surat yang tersedia | Mendefinisikan LetterSubmission |
| LetterSubmission | Pengajuan surat yang dibuat | Memiliki Attachment, ActivityLog, di-submit oleh User |
| Attachment | File lampiran yang diunggah | Milik LetterSubmission |
| ActivityLog | Log aktivitas pada pengajuan | Milik LetterSubmission dan User |
| LetterService | Service layer untuk bisnis logic | Mengelola LetterSubmission, Attachment, ActivityLog |
| LetterSubmissionPolicy | Policy untuk otorisasi | Mengontrol akses LetterSubmission |

## Status Transitions

```
pending → staff_reviewed (jika requires_dekan_approval)
pending → approved (jika tidak requires_dekan_approval)
pending → rejected
staff_reviewed → approved (oleh dekan)
staff_reviewed → rejected (oleh dekan)
```
