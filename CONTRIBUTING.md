# Contributing to CMPI Backend

Thank you for considering contributing to CMPI Backend! This document provides guidelines and instructions for contributing.

## Code of Conduct

This project and everyone participating in it is governed by our [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## How to Contribute

### Reporting Bugs

- Check existing issues to avoid duplicates
- Open a new issue with a clear title and description
- Include steps to reproduce the bug
- Include your environment details (OS, PHP version, etc.)

### Suggesting Features

- Open an issue describing the feature
- Explain why this feature would be useful
- Include example use cases if possible

### Pull Requests

1. Fork the repository
2. Create your feature branch from `main`:
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. Make your changes following the coding standards below
4. Commit your changes with a descriptive message:
   ```bash
   git commit -m 'feat: add amazing feature'
   ```
5. Push to your branch:
   ```bash
   git push origin feature/amazing-feature
   ```
6. Open a Pull Request

## Development Setup

### Prerequisites
- PHP 8.2+
- Composer
- MySQL or PostgreSQL
- Node.js (for asset compilation)

### Installation
```bash
git clone https://github.com/salahuddingfx/CMPI-Backend.git
cd CMPI-Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Coding Standards

### PHP / Laravel
- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style
- Use meaningful variable and method names
- Add PHPDoc blocks for complex methods
- Keep controllers thin; use Form Requests and Resources
- Use Eloquent relationships instead of raw queries where possible

### Commits

Follow [Conventional Commits](https://www.conventionalcommits.org/):
- `feat:` new feature
- `fix:` bug fix
- `docs:` documentation changes
- `style:` formatting changes
- `refactor:` code refactoring
- `test:` adding tests
- `chore:` maintenance tasks

## Security

Please do not report security vulnerabilities through public GitHub issues. See our [Security Policy](SECURITY.md) for details.

## License

By contributing, you agree that your contributions will be licensed under the [GNU General Public License v3.0](LICENSE).
