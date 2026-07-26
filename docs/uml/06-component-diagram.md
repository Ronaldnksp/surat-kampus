# Component Diagram - Arsitektur Aplikasi

## Component Diagram (Mermaid)

```mermaid
graph TB
    subgraph "Presentation Layer"
        direction TB
        F[Filament Admin Panel]
        W[Web Routes]
        API[API Routes]
    end

    subgraph "Application Layer"
        direction TB
        C[Controllers]
        MW[Middleware]
        R[Resources]
    end

    subgraph "Domain/Business Layer"
        direction TB
        LS[LetterService]
        LP[LetterSubmissionPolicy]
        N[Notifications]
        J[Jobs]
    end

    subgraph "Data Layer"
        direction TB
        M[Models]
        DB[(Database)]
        FS[(File Storage)]
    end

    subgraph "Infrastructure"
        direction TB
        Q[Queue]
        Cache[Cache]
        Mail[Mail Service]
    end

    F --> C
    W --> C
    API --> C
    
    C --> MW
    C --> R
    C --> LS
    
    MW --> LP
    
    LS --> M
    LS --> N
    LS --> J
    
    N --> Mail
    J --> Q
    
    M --> DB
    M --> FS
    
    Q --> Cache
    
    style F fill:#2196F3,color:#fff
    style W fill:#2196F3,color:#fff
    style API fill:#2196F3,color:#fff
    style C fill:#4CAF50,color:#fff
    style MW fill:#4CAF50,color:#fff
    style R fill:#4CAF50,color:#fff
    style LS fill:#FF9800,color:#fff
    style LP fill:#FF9800,color:#fff
    style N fill:#FF9800,color:#fff
    style J fill:#FF9800,color:#fff
    style M fill:#9C27B0,color:#fff
    style DB fill:#9C27B0,color:#fff
    style FS fill:#9C27B0,color:#fff
    style Q fill:#607D8B,color:#fff
    style Cache fill:#607D8B,color:#fff
    style Mail fill:#607D8B,color:#fff
```

## Deskripsi Komponen

### Presentation Layer
| Komponen | Fungsi |
|----------|--------|
| Filament Admin Panel | Admin panel untuk semua role |
| Web Routes | Route web untuk akses publik |
| API Routes | API endpoint untuk integrasi |

### Application Layer
| Komponen | Fungsi |
|----------|--------|
| Controllers | Handle request dan response |
| Middleware | Autentikasi, otorisasi, rate limiting |
| Resources | Transform data untuk response |

### Domain/Business Layer
| Komponen | Fungsi |
|----------|--------|
| LetterService | Bisnis logic pengajuan surat |
| LetterSubmissionPolicy | Otorisasi akses |
| Notifications | Notifikasi email dan in-app |
| Jobs | Background processing |

### Data Layer
| Komponen | Fungsi |
|----------|--------|
| Models | Eloquent ORM models |
| Database | MySQL/PostgreSQL |
| File Storage | Storage lampiran |

### Infrastructure
| Komponen | Fungsi |
|----------|--------|
| Queue | Async job processing |
| Cache | Performance optimization |
| Mail Service | Email delivery |

## Arsitektur Filament Panels

```mermaid
graph LR
    subgraph "Filament Panels"
        MP[Mahasiswa Panel]
        SP[Staff Panel]
        DP[Dekan Panel]
    end

    subgraph "Shared Resources"
        LSR[LetterSubmissionResource]
        LTR[LetterTypeResource]
        UR[UserResource]
    end

    MP --> LSR
    SP --> LSR
    SP --> LTR
    SP --> UR
    DP --> LSR
    
    style MP fill:#4CAF50,color:#fff
    style SP fill:#FF9800,color:#fff
    style DP fill:#f44336,color:#fff
```

## Deployment Architecture

```mermaid
graph TB
    subgraph "Cloud Infrastructure"
        LB[Load Balancer]
        
        subgraph "Compute"
            APP1[App Server 1]
            APP2[App Server 2]
        end
        
        subgraph "Database"
            DB[(Primary DB)]
            DBR[(Replica DB)]
        end
        
        subgraph "Storage"
            S3[Object Storage]
        end
        
        subgraph "Services"
            QUEUE[Queue Worker]
            CACHE[Redis Cache]
            MAIL[Mail Service]
        end
    end

    Client[Client] --> LB
    LB --> APP1
    LB --> APP2
    APP1 --> DB
    APP2 --> DB
    DB --> DBR
    APP1 --> S3
    APP2 --> S3
    APP1 --> QUEUE
    APP2 --> QUEUE
    APP1 --> CACHE
    APP2 --> CACHE
    APP1 --> MAIL
    APP2 --> MAIL
    
    style Client fill:#2196F3,color:#fff
    style LB fill:#4CAF50,color:#fff
    style APP1 fill:#FF9800,color:#fff
    style APP2 fill:#FF9800,color:#fff
    style DB fill:#9C27B0,color:#fff
    style DBR fill:#9C27B0,color:#fff
    style S3 fill:#9C27B0,color:#fff
    style QUEUE fill:#607D8B,color:#fff
    style CACHE fill:#607D8B,color:#fff
    style MAIL fill:#607D8B,color:#fff
```
