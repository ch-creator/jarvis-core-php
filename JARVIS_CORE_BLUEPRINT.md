# JARVIS CORE

## Core PHP Cloud Platform -- Software Blueprint (v1.0)

> **Purpose:** Build the cloud backend ("Jarvis Core") that powers every
> client (Flutter app, desktop agent, web dashboard, and future
> integrations). The desktop agent is **not** part of this phase.

------------------------------------------------------------------------

# 1. Vision

Jarvis Core is the central brain of the Jarvis ecosystem.

Responsibilities:

-   Authentication
-   AI Provider Management
-   Chat & Streaming
-   Missions
-   Memory
-   Devices
-   Notifications
-   APIs
-   WebSocket Communication
-   Security
-   Audit Logs

All clients communicate **only** with Jarvis Core.

    Flutter App
          │
    Web Dashboard
          │
    Desktop Agent (Future)
          │
    ──────────────
      Jarvis Core
    ──────────────
          │
    AI Providers
    (OpenAI, Claude, Gemini, NVIDIA NIM, Ollama, OpenRouter)

------------------------------------------------------------------------

# 2. Technology Stack

-   PHP 8.4+
-   Core PHP (custom MVC)
-   Composer
-   SQLite (development)
-   MySQL/PostgreSQL (production)
-   JWT Authentication
-   REST API
-   WebSocket
-   PSR-4 Autoloading
-   Monolog
-   Dotenv

------------------------------------------------------------------------

# 3. Suggested Project Structure

``` text
jarvis-core/
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Providers/
│   ├── Middleware/
│   ├── Jobs/
│   ├── Events/
│   ├── Helpers/
│   └── Core/
│
├── config/
├── database/
├── modules/
├── public/
├── routes/
├── storage/
├── tests/
├── vendor/
└── websocket/
```

------------------------------------------------------------------------

# 4. Development Phases

## Phase 1 -- Foundation

-   Project bootstrap
-   Routing
-   Dependency Injection
-   Configuration
-   Environment Loader
-   Database Layer
-   Logger
-   Error Handler
-   Authentication Skeleton

## Phase 2 -- Authentication

Features:

-   Register
-   Login
-   JWT
-   Refresh Token
-   Logout
-   Password Reset
-   Device Registration

## Phase 3 -- User Module

-   Profile
-   Preferences
-   Avatar
-   Theme
-   AI Defaults

## Phase 4 -- AI Engine

Create a provider abstraction:

``` php
interface AIProvider
{
    public function chat(array $messages): mixed;
    public function stream(array $messages): mixed;
    public function vision(array $payload): mixed;
}
```

Providers:

-   OpenAI
-   Claude
-   Gemini
-   NVIDIA NIM
-   Ollama
-   OpenRouter

## Phase 5 -- Chat

-   Conversations
-   Messages
-   Attachments
-   Streaming
-   Voice
-   Images

## Phase 6 -- Mission Engine

Mission Lifecycle:

1.  Created
2.  Queued
3.  Running
4.  Completed
5.  Failed
6.  Cancelled

Each mission stores:

-   Prompt
-   Context
-   Logs
-   Files
-   AI Provider
-   Output

## Phase 7 -- Memory

-   Short-term conversation memory
-   Long-term summaries
-   Semantic search (future)

## Phase 8 -- Devices

Initially:

-   Phone
-   Laptop (manual)
-   Desktop (manual)

Future:

-   Agent heartbeat
-   Online/offline
-   Capabilities

## Phase 9 -- Notifications

-   Mission completed
-   Mission failed
-   Device connected
-   Updates

## Phase 10 -- WebSocket

Support:

-   Streaming AI responses
-   Mission progress
-   Device status
-   Push notifications

## Phase 11 -- Admin Dashboard

Sections:

-   Dashboard
-   Users
-   Devices
-   Missions
-   AI Usage
-   Logs
-   Settings

------------------------------------------------------------------------

# 5. Database (Initial)

Core tables:

-   users
-   devices
-   conversations
-   messages
-   missions
-   mission_logs
-   ai_providers
-   api_keys
-   notifications
-   settings
-   audit_logs

------------------------------------------------------------------------

# 6. API Design

Versioned API:

    /api/v1

Groups:

-   /auth
-   /users
-   /chat
-   /missions
-   /devices
-   /notifications
-   /settings

------------------------------------------------------------------------

# 7. Security

-   JWT Authentication
-   Rate Limiting
-   HTTPS Only
-   Encrypted Secrets
-   Audit Logging
-   Input Validation
-   Role-based authorization (future)

------------------------------------------------------------------------

# 8. Non-Goals (Phase 1)

Do **not** build:

-   Desktop automation
-   Cursor integration
-   Mouse/keyboard control
-   Screen capture
-   Browser automation

These belong to later phases.

------------------------------------------------------------------------

# 9. Success Criteria

The backend is considered MVP-ready when it can:

-   Authenticate users
-   Manage AI providers
-   Send and stream AI chats
-   Create and track missions
-   Upload files
-   Send notifications
-   Expose stable REST/WebSocket APIs
-   Be deployed to a VPS without code changes

------------------------------------------------------------------------

# 10. Long-Term Vision

Jarvis Core will become the permanent cloud brain for every future
client:

-   Flutter Mobile
-   Web Dashboard
-   Desktop Agent
-   Future Voice Assistant
-   Future Browser Extension
-   Future Multi-Agent System

Every component should communicate through secure APIs, making Jarvis
Core the single source of truth.
