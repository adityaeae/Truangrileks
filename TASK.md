# HERMES DEVELOPMENT TASK

## RUANGRILEKS — CORE MVP ONLY

Act as a Laravel full-stack developer.

Build the CORE MVP of a web application named RuangRileks.

IMPORTANT:
Your task is only to build the foundational structure and essential features. Do not implement every advanced feature.

The project will be continued by other coding assistants, so maintain clean, readable, and well-organized source code.

WORKING MODE:
This task is done in phases (Tahap 1 to 4). Only work on the phase given in the current instruction, then STOP and report. Never continue to the next phase on your own.

---

## 1. PROJECT OVERVIEW

RuangRileks is a web-based academic stress screening information system for Indonesian high school students.

Tagline:
"Kenali tekanan akademikmu, pahami dirimu, dan temukan langkah yang tepat."

Main purpose:

- Help students complete academic stress screening.
- Display screening results.
- Provide simple educational recommendations.

This application is an educational screening tool, not a medical diagnosis system.

---

## 2. TECHNOLOGY STACK

Use:

- Laravel 12.
- PHP 8.2+.
- MySQL / MariaDB.
- Blade.
- Tailwind CSS 4 melalui Vite (sudah digunakan project).
- Custom CSS seperlunya.
- Vanilla JavaScript.

Target database:
ruangrileks_v2

Target environment (UPDATED, replaces any mention of XAMPP):

- Ubuntu running inside WSL2 on Windows 11.
- PHP CLI, Composer, and local MariaDB/MySQL installed in Ubuntu.
- Run the app with `php artisan serve`.
- Do NOT use XAMPP.
- If database credentials are needed, ask the user. Never invent credentials and never store passwords in public or committed files (use `.env` only, which must stay git-ignored).

Do not install unnecessary packages.

Do not use React, Vue, Tailwind, or external AI APIs.

---

## 3. DESIGN

Create a simple, modern, clean, and responsive website.

Visual style:

- Muted teal green.
- Warm beige.
- White background.
- Dark gray typography.
- Rounded cards.
- Subtle shadows.
- Minimalist layout.

Use Indonesian throughout the interface.

Ensure good mobile and desktop responsiveness.

---

## 4. CORE FEATURES

### A. Public Website

Create:

1. Homepage.
2. About page.
3. Education page.
4. BK information page.

Homepage must include:

- Navigation.
- Hero section.
- Introduction.
- Main screening button.
- Screening process explanation.
- Educational content preview.
- Footer.

### B. Authentication and User Roles

Implement basic authentication for:

- Admin.
- Student.
- BK teacher.

Implement:

- Registration for students.
- Login.
- Logout.
- Role-based dashboard redirection.
- Basic profile information.

Create simple placeholder dashboards for each role.

Do not implement advanced dashboard features.

### C. Screening Module

Create a basic screening workflow:

1. Screening information.
2. Screening consent.
3. Questionnaire.
4. Result.

Allow visitors to try the DEMO screening without registering.

Create a 16-question placeholder questionnaire based on four planned dimensions:

- Parent-Related Pressure.
- Internal-Related Pressure.
- Friend-Related Pressure.
- Teacher-Related Pressure.

IMPORTANT:

- Do not invent official SIVASS questions.
- Use clearly labeled DEMO placeholder questions.
- Do not invent official SIVASS scoring or interpretation thresholds.
- Keep DEMO scoring isolated in a simple service class.
- Do not claim that DEMO results are scientifically validated.
- Validate all required answers on the server.

### D. Screening Result

Create a simple result page containing:

- Screening date.
- DEMO result summary.
- Educational recommendations.
- Disclaimer.

Display:
"Ini merupakan hasil DEMO dan bukan hasil penilaian psikologis yang tervalidasi."

### E. Basic Data Storage

Prepare the basic database structure needed for:

- Users.
- Screening results.
- Screening answers.
- Screening consent.

Separate consent for:

1. Participating in screening.
2. Permanently saving results.
3. Sharing a specific result with BK.

For this stage, implement screening participation consent and the basic optional storage foundation.

Never permanently store screening answers or results when storage consent is declined.

Do not implement BK result-sharing functionality yet.

---

## 5. FEATURES EXPLICITLY OUT OF SCOPE

Do NOT implement these features in this task:

- Advanced BK dashboard.
- Result-sharing workflow.
- Revoking BK access.
- Advanced screening history.
- Complex admin management.
- Advanced article management.
- Statistical analytics.
- Chatbot.
- AI-based psychological analysis.
- External integrations.
- Notifications.
- Complex reporting.

Prepare a clean structure so these features can be added later.

---

## 6. CODE QUALITY

Requirements:

- Use Laravel MVC conventions.
- Use clear controller and model names.
- Use named routes.
- Use Blade layouts and reusable components.
- Use migrations and Eloquent relationships.
- Validate all important inputs.
- Use password hashing.
- Apply authentication middleware.
- Apply basic role authorization.
- Keep controllers simple.
- Keep DEMO calculation separate from controllers.
- Avoid duplicated code.

Do not place all logic inside one controller.

---

## 7. PROJECT DOCUMENTATION

Create three concise files:

**AGENTS.md:**
Project rules and coding conventions.

**PROJECT_STATE.md:**
Current implementation status, database structure, remaining features, and known issues.

**README.md:**
Basic setup and instructions to run the application (Ubuntu/WSL).

These documents are important because other AI coding assistants will continue this project.

---

## 8. DEVELOPMENT WORKFLOW

First inspect the existing workspace.

Do not recreate Laravel if it already exists.

Do not overwrite existing useful code.

Implement only the core MVP features listed above.

Run relevant migrations and tests only when safe.

Never use `migrate:fresh` or destructive database commands without explicit approval.

Verify:

- Laravel starts successfully.
- Public pages load.
- Login works.
- Role-based redirection works.
- Screening can be completed.
- DEMO results appear.
- Declining storage does not permanently save results.
- Unauthorized access is rejected.

Do not claim successful tests unless actually executed.

---

## 9. FINAL REPORT

After implementation, provide a concise report containing:

1. Features completed.
2. Files created or modified.
3. Database tables created.
4. Commands needed to run the application.
5. Tests performed and their actual results.
6. Known issues.
7. Recommended next development tasks.

Do not add unrequested features.

Do not spend unnecessary tokens on lengthy explanations.

Prioritize a functional, clean, maintainable CORE MVP.

---

## 10. PHASES

- **Tahap 1:** Foundation. Install/verify Laravel 12, database connection, migrations (users, screening_results, screening_answers, screening_consents), authentication, roles, role-based redirects, placeholder dashboards.
- **Tahap 2:** Public website. Blade layout and components, design, Homepage, About, Education, BK information pages.
- **Tahap 3:** Screening module. Information, consent, questionnaire, DEMO scoring service, result page, storage consent behavior.
- **Tahap 4:** Documentation and verification. AGENTS.md, PROJECT_STATE.md, README.md, full verification checklist, final report.

BEGIN BY INSPECTING THE PROJECT AND WAIT FOR THE PHASE INSTRUCTION.
