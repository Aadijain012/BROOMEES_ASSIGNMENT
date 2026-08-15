# Broomees Control Center

Broomees Control Center is a responsive frontend control surface for the **Broomees User Relationship, Reputation & Access-Control System**. It presents the API assignment as a dark developer dashboard with an API contract register, reputation model, readiness states, and a mobile-friendly navigation experience.

> This repository contains the frontend dashboard only. It intentionally does not implement the Laravel, PostgreSQL, Redis, authentication, or API layers described in the engineering assignment. The interface is ready to be connected to those endpoints once the backend exists.

## Technology

The project uses React 19, TypeScript, Vite, Tailwind CSS 4, Framer Motion, and Lucide icons. It uses generated visual assets hosted through the project storage URLs referenced in the application source.

## Local development

Install dependencies with `pnpm install`, then run `pnpm dev`. The dashboard is served on the local Vite development URL. Create a production build with `pnpm build`; type-check the project with `pnpm check`.

| Command | Purpose |
| --- | --- |
| `pnpm install` | Install project dependencies. |
| `pnpm dev` | Start the Vite development server. |
| `pnpm check` | Run TypeScript validation. |
| `pnpm build` | Produce a production build. |

## Key source files

| File | Responsibility |
| --- | --- |
| `client/src/components/ControlCenter.tsx` | Main dashboard interface, interactions, and operational content. |
| `client/src/pages/Home.tsx` | Dashboard route entry point. |
| `client/src/index.css` | Architectural Nightshift tokens, typography, and accessibility motion rules. |
| `ideas.md` | Chosen visual design direction and brand decisions. |

## Connecting the API

The dashboard is currently designed to bind to the Laravel API described in the assignment. The most visible integration points are `GET /api/metrics/reputation`, the user-resource endpoints, the token endpoint, the health endpoint, and Swagger documentation at `/api/documentation`.
