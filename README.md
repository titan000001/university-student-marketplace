# University Student Marketplace (UniMarket)

A closed, campus-exclusive peer-to-peer marketplace web platform for university students.

## Project Overview

UniMarket addresses peer-to-peer commerce challenges on university campuses by enforcing student identity authentication and establishing a zero-monetary-risk "Reserve & Meet" exchange framework.

## Project Architecture

```
university-student-marketplace/
├── frontend/             # Single Page Application (SPA) frontend modules
│   ├── assets/           # Static media assets (images, icons, logos)
│   ├── css/              # Tailwind CSS styles and compilation
│   └── js/               # ES module frontend scripts (router, components, services)
├── backend/              # Node.js/Express MVC backend architecture
│   ├── config/           # Database and application settings
│   ├── controllers/      # Request handlers & business logic
│   ├── middleware/       # JWT auth, rate limiting, security headers
│   ├── models/           # MySQL data queries & ACID transaction logic
│   ├── routes/           # RESTful API route declarations
│   ├── services/         # Shared business services (e.g. price analytics)
│   ├── socket/           # Socket.IO real-time chat handlers
│   └── uploads/          # Designated static file directory for listing images
├── database/             # Relational MySQL migrations and seeds
│   ├── migrations/       # DDL table creation and constraint scripts
│   └── seeds/            # Initial development seed data
├── docs/                 # Project documentation and specifications
│   ├── PROJECT_BLUEPRINT.md # Master blueprint for Capstone project
│   └── architecture/     # Architectural diagrams and specifications
├── .env.example          # Environment configuration template
├── package.json          # Node.js package manifests & dependencies
└── README.md             # Project README
```

## Foundation Status

Initial folder structure and dependency configurations established according to SRS specification.