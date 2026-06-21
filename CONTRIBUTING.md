# 🤝 Contributing to CMPI Backend

We are excited that you want to contribute to the **CMPI Backend API** project! Your support makes our software better for Cox's Bazar Model Polytechnic Institute and other schools.

Please take a moment to review this document to ensure your contributions align with our quality, code styling, and design standards.

---

## 🛠️ Contribution Workflow

We follow a standard fork-and-pull-request model:
1.  **Fork the Repository**: Create a personal copy of the repository on GitHub.
2.  **Create a Branch**: Create a feature branch off of the `main` branch.
    *   For new features: `feature/your-feature-name`
    *   For bug fixes: `bugfix/your-bug-name`
    *   For hotfixes: `hotfix/your-hotfix-name`
3.  **Implement Your Changes**: Write clean, self-documenting code. Refer to the style guides below.
4.  **Validate Locally**:
    *   Backend linting: `php -l path/to/file.php` or `php artisan route:list` to verify route compile.
5.  **Commit Your Work**: Follow conventional commit guidelines (e.g., `feat: ...`, `fix: ...`, `docs: ...`, `refactor: ...`).
6.  **Submit a Pull Request (PR)**: Target the `main` branch. Provide a clear summary of your changes, links to related issues, and any validation logs.

---

## 🎨 Style Guides

To keep our codebase readable and maintainable, all contributions must strictly conform to these styling and structural guides.

### 🐘 PHP (Backend)
*   **Standards**: Follow **PSR-12** styling guidelines.
*   **Safety**: Avoid raw SQL queries; use Eloquent ORM or Query Builder with parameterized bindings.
*   **RBAC**: When adding backend routes, ensure you restrict access by checking the administrator's permission using:
    ```php
    if ($request->user()->role !== 'admin' || !$request->user()->hasPermission('module_name')) {
        return response()->json(['message' => 'Unauthorized.'], 403);
    }
    ```

---

## 📝 Commit Guidelines

We use [Conventional Commits](https://www.conventionalcommits.org/) format:

*   `feat`: A new feature for the application.
*   `fix`: A bug fix.
*   `docs`: Documentation changes.
*   `style`: Code style modifications (formatting, white-space, semi-colons).
*   `refactor`: Code changes that neither fix a bug nor add a feature.
*   `test`: Adding or correcting tests.
*   `chore`: Updating build scripts, dependencies, config files.

---

## 💬 Communication & Questions

If you have any questions, feel free to contact the author or open an issue:
*   **Lead Developer**: Salah Uddin Kader
*   **Email**: **salahuddin.dev@gmail.com**
*   **Issues**: Open a ticket on GitHub explaining the feature or bug you're addressing.
