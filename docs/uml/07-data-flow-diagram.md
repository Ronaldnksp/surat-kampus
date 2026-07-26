# Data Flow Diagram (ERD) - Sistem Pengajuan Surat

## Entity Relationship Diagram (Mermaid)

```mermaid
erDiagram
    USERS {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        enum role
        varchar nim UK
        varchar nip UK
        varchar phone
        varchar department
        boolean is_active
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    LETTER_TYPES {
        bigint id PK
        varchar name
        text description
        boolean requires_dekan_approval
        int max_days_to_review
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    LETTER_SUBMISSIONS {
        bigint id PK
        bigint user_id FK
        bigint letter_type_id FK
        varchar subject
        text body
        enum status
        text rejection_reason
        bigint approved_by FK
        bigint dekan_approved_by FK
        date due_date
        timestamp created_at
        timestamp updated_at
    }

    ATTACHMENTS {
        bigint id PK
        bigint letter_submission_id FK
        varchar filename
        varchar original_name
        varchar mime_type
        bigint size
        varchar path
        timestamp created_at
        timestamp updated_at
    }

    ACTIVITY_LOGS {
        bigint id PK
        bigint letter_submission_id FK
        bigint user_id FK
        varchar action
        text description
        json old_values
        json new_values
        timestamp created_at
        timestamp updated_at
    }

    NOTIFICATIONS {
        varchar id PK
        varchar type
        varchar notifiable_type
        bigint notifiable_id
        text data
        timestamp read_at
        timestamp created_at
        timestamp updated_at
    }

    USERS ||--o{ LETTER_SUBMISSIONS : "submits"
    USERS ||--o{ LETTER_SUBMISSIONS : "approves"
    USERS ||--o{ LETTER_SUBMISSIONS : "dekan_approves"
    USERS ||--o{ ACTIVITY_LOGS : "creates"
    USERS ||--o{ NOTIFICATIONS : "receives"

    LETTER_TYPES ||--o{ LETTER_SUBMISSIONS : "defines"

    LETTER_SUBMISSIONS ||--o{ ATTACHMENTS : "contains"
    LETTER_SUBMISSIONS ||--o{ ACTIVITY_LOGS : "logs"
    LETTER_SUBMISSIONS ||--o{ NOTIFICATIONS : "triggers"
```

## Data Flow Diagram (Mermaid)

```mermaid
flowchart LR
    subgraph "Input"
        M[Mahasiswa]
        S[Staff]
        D[Dekan]
    end

    subgraph "Processes"
        P1[Login]
        P2[Submit Surat]
        P3[Review Surat]
        P4[Approve/Reject]
        P5[Generate PDF]
        P6[Send Notification]
    end

    subgraph "Data Stores"
        DS1[(Users)]
        DS2[(Letter Types)]
        DS3[(Submissions)]
        DS4[(Attachments)]
        DS5[(Activity Logs)]
        DS6[(Notifications)]
    end

    subgraph "Output"
        O1[Email]
        O2[In-App Notification]
        O3[PDF Surat]
        O4[Dashboard Stats]
    end

    M --> P1
    S --> P1
    D --> P1
    
    P1 --> DS1
    P1 -->|Authenticated| P2
    P1 -->|Authenticated| P3
    P1 -->|Authenticated| P4
    
    M --> P2
    P2 --> DS3
    P2 --> DS4
    P2 --> DS5
    
    S --> P3
    D --> P3
    P3 --> DS3
    
    S --> P4
    D --> P4
    P4 --> DS3
    P4 --> DS5
    P4 --> P6
    
    P4 -->|Approved| P5
    P5 --> O3
    
    P6 --> DS6
    P6 --> O1
    P6 --> O2
    
    DS3 --> O4
    
    style M fill:#4CAF50,color:#fff
    style S fill:#FF9800,color:#fff
    style D fill:#f44336,color:#fff
    style P1 fill:#2196F3,color:#fff
    style P2 fill:#2196F3,color:#fff
    style P3 fill:#2196F3,color:#fff
    style P4 fill:#2196F3,color:#fff
    style P5 fill:#2196F3,color:#fff
    style P6 fill:#2196F3,color:#fff
    style DS1 fill:#9C27B0,color:#fff
    style DS2 fill:#9C27B0,color:#fff
    style DS3 fill:#9C27B0,color:#fff
    style DS4 fill:#9C27B0,color:#fff
    style DS5 fill:#9C27B0,color:#fff
    style DS6 fill:#9C27B0,color:#fff
    style O1 fill:#607D8B,color:#fff
    style O2 fill:#607D8B,color:#fff
    style O3 fill:#607D8B,color:#fff
    style O4 fill:#607D8B,color:#fff
```

## Deskripsi Data Flow

### Input
| Input | Sumber | Proses |
|-------|--------|--------|
| Login | Semua role | Autentikasi user |
| Submit Surat | Mahasiswa | Membuat pengajuan baru |
| Review Surat | Staff/Dekan | Meninjau pengajuan |
| Approve/Reject | Staff/Dekan | Menyetujui/menolak pengajuan |

### Processes
| Proses | Input | Output | Data Store |
|--------|-------|--------|------------|
| Login | Credentials | Session | Users |
| Submit Surat | Form data + files | Submission record | Submissions, Attachments, Activity Logs |
| Review Surat | Submission ID | Detail view | Submissions |
| Approve/Reject | Decision + reason | Status update | Submissions, Activity Logs |
| Generate PDF | Submission data | PDF file | File Storage |
| Send Notification | Notification data | Email/In-App | Notifications |

### Output
| Output | Tujuan | Jenis |
|--------|--------|-------|
| Email | User/Staff/Dekan | Notifikasi status |
| In-App Notification | User/Staff/Dekan | Real-time update |
| PDF Surat | User | Dokumen resmi |
| Dashboard Stats | Semua role | Statistik |
