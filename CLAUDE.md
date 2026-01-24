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
