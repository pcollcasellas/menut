# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Menut is a weekly meal planning application built with Laravel 12, Livewire 3, and Tailwind CSS. Users can manage recipes and assign them to lunch/dinner slots across a weekly calendar view.

## Commands

### Development
```bash
composer dev          # Runs server, queue, logs (pail), and vite concurrently
```

### Testing
```bash
composer test         # Clears config cache and runs PHPUnit tests
php artisan test --filter=TestName  # Run a single test
```

### Build & Setup
```bash
composer setup        # Full setup: install deps, generate key, migrate, build assets
npm run build         # Production asset build
npm run dev           # Vite dev server with HMR
```

### Code Quality
```bash
./vendor/bin/pint     # Laravel Pint code formatting
```

## Git Workflow

### Feature Development
- **Always create a new branch** for each feature or fix
- Branch naming convention: `feature/description` or `fix/description`
- After completing work on a branch, **create a Pull Request** to merge into `dev`
- Do not merge directly to dev

### Workflow Steps
1. Create a new branch from dev: `git checkout -b feature/feature-name`
2. Implement the feature with commits
3. Push the branch to remote
4. Create a Pull Request targeting `dev`
5. After PR approval and merge, pull latest dev and delete the feature branch

### PR Review Process
When reviewing a Pull Request as code advisor:
- Run a dev server using `composer dev`
- Test the new functionalities in the browser using Playwright MCP
- Verify all acceptance criteria are met
- Check for any regressions or unintended side effects

## Testing & Quality Assurance

### Testing Requirements
When implementing changes, **always**:
1. **Write unit tests** for new functionality or bug fixes using PHPUnit
   - Implement **happy path tests**: verify expected behavior with valid inputs
   - Implement **sad path tests**: verify error handling with invalid inputs, edge cases, and failure scenarios
2. **Test in the browser** using the Playwright MCP to verify:
   - UI/UX works as expected
   - Livewire interactions function correctly
   - Responsive design works across different screen sizes
   - No console errors or warnings

### Testing Workflow
- Run `composer test` to verify unit tests pass
- Use Playwright MCP tools to navigate and interact with the application
- Verify the changes work end-to-end before creating a PR

## Architecture

### Domain Models
- **Recipe**: Stores meal recipes (name, description, ingredients, instructions)
- **MenuItem**: Links a recipe to a specific date and meal_type (lunch/dinner). Unique constraint on date+meal_type ensures one recipe per slot.

### Livewire Components (`app/Livewire/`)
- **WeeklyMenu**: Main dashboard component. Displays 7-day grid with navigation between weeks. Listens for `menu-updated` event to refresh.
- **MealSlot**: Individual slot component for each day/meal_type combination. Handles recipe selection and emits `menu-updated` when changed.
- **RecipeManager**: CRUD interface for recipes at `/recipes` route.

### Key Patterns
- Livewire Volt is installed but components use class-based syntax in `app/Livewire/`
- Views follow Livewire convention in `resources/views/livewire/`
- Authentication provided by Laravel Breeze with Livewire stack
- Tests use in-memory SQLite (configured in phpunit.xml)
