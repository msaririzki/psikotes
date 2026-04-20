# AGENTS.md

## Project
Aplikasi belajar dan simulasi psikotes Polri berbasis Laravel full-stack.

## Stack
- Laravel 13
- PHP 8.3+
- Inertia.js
- Vue 3
- TypeScript
- Tailwind CSS
- MySQL

## Rules
- Follow Laravel conventions
- Use Form Request for validation
- Use Policies for authorization
- Use Service classes for business logic
- Keep controllers thin
- Keep migrations clean and explicit
- Use enums when appropriate
- Prefer readable, maintainable code over clever code
- Do not implement unrelated features
- Work in phases, not all at once

## Workflow
- Read project context first
- Plan before coding
- Implement only requested phase
- After coding, summarize files changed
- Explain architectural decisions briefly

## Current priority
Build Phase 1 only:
- auth
- email verification
- roles
- dashboard base
- core schema