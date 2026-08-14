# TAMAKAN

TAMAKAN is an interactive quiz platform connecting learners and educators through topic-based quizzes, feedback, scoring, and learner-recommended questions.

> Learn. Practice. Improve.

## Project Structure

```text
TAMAKAN/
├── pages/
│   ├── auth/        # Entry, authentication, logout
│   ├── learner/     # Learner-facing pages
│   └── educator/    # Educator-facing pages
├── actions/
│   ├── learner/     # Learner form/action handlers
│   └── educator/    # Educator CRUD/action handlers
├── api/             # AJAX / data endpoints
├── includes/        # Shared config, header, footer, auth guards
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── videos/
├── storage/
│   └── uploads/
├── database/
│   └── tamakan.sql
├── docs/
│   └── screenshots/
└── README.md
```

## Main Roles

- **Learners** browse quizzes, take quizzes, receive scores, submit feedback, and recommend questions.
- **Educators** manage quizzes and questions, review recommendations, and monitor learner feedback.

## Technologies

PHP, MySQL, HTML, CSS, JavaScript, jQuery, AJAX / Fetch API.

> Note: This repository has been reorganized into folders for presentation on GitHub. Because the original project used relative paths based on a flat folder structure, some internal paths may need updating before running the reorganized version.
