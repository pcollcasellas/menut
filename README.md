# Menut

A weekly meal planning application built with Laravel 12, Livewire 3, and Tailwind CSS. Plan your meals, manage recipes, and say goodbye to the daily question: "What are we eating today?"

## Features

- 📅 **Weekly Calendar View** - Visualize your entire week's menu at a glance
- 🍽️ **Meal Management** - Organize lunch and dinner for each day
- 📖 **Recipe Library** - Store recipes with ingredients and instructions
- 📱 **Responsive Design** - Mobile-first design that works on all devices
- 🎨 **Modern UI** - Clean interface built with Tailwind CSS
- ⚡ **Real-time Updates** - Livewire provides seamless interactivity

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Livewire 3, Tailwind CSS
- **Authentication**: Laravel Breeze
- **Database**: SQLite (development), supports MySQL/PostgreSQL for production

## Quick Start

### Prerequisites

- PHP 8.3+
- Composer
- Node.js & NPM
- SQLite (for local development)

### Installation

1. Clone the repository
```bash
git clone https://github.com/pcollcasellas/menut.git
cd menut
```

2. Run the complete setup
```bash
composer setup
```

This command will:
- Install PHP dependencies
- Install NPM dependencies
- Generate application key
- Run database migrations
- Build frontend assets

### Development

Start the development server with hot module reloading:

```bash
composer dev
```

This runs multiple services concurrently:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server with HMR

### Testing

Run the test suite:

```bash
composer test
```

Run a specific test:

```bash
php artisan test --filter=TestName
```

### Code Quality

Format code with Laravel Pint:

```bash
./vendor/bin/pint
```

## Project Structure

### Domain Models

- **Recipe**: Stores meal recipes with name, description, ingredients, and instructions
- **MenuItem**: Links recipes to specific dates and meal types (lunch/dinner)

### Livewire Components

Located in `app/Livewire/`:

- **WeeklyMenu**: Main dashboard displaying the 7-day grid
- **MealSlot**: Individual slot for each day/meal combination
- **RecipeManager**: CRUD interface for recipe management

### Views

Livewire views are in `resources/views/livewire/` following standard Livewire conventions.

## Git Workflow

This project follows a feature branch workflow:

1. Create a new branch from `dev`:
```bash
git checkout dev
git pull origin dev
git checkout -b feature/your-feature-name
```

2. Make your changes and commit:
```bash
git add .
git commit -m "Your descriptive commit message"
```

3. Push to remote:
```bash
git push -u origin feature/your-feature-name
```

4. Create a Pull Request targeting the `dev` branch

5. After PR approval and merge, clean up:
```bash
git checkout dev
git pull origin dev
git branch -d feature/your-feature-name
```

### Branch Naming Convention

- `feature/description` - New features
- `fix/description` - Bug fixes

## Testing Requirements

All code changes should include:

1. **Unit Tests**
   - Happy path tests for expected behavior
   - Sad path tests for error handling and edge cases

2. **Browser Testing**
   - Test functionality manually using Playwright MCP
   - Verify UI/UX works as expected
   - Check Livewire interactions
   - Test responsive design across screen sizes
   - Verify no console errors

## Available Commands

### Development
- `composer dev` - Run all development services concurrently
- `npm run dev` - Vite dev server with HMR
- `npm run build` - Production asset build

### Testing
- `composer test` - Run PHPUnit test suite
- `php artisan test --filter=TestName` - Run specific test

### Setup
- `composer setup` - Complete project setup

### Code Quality
- `./vendor/bin/pint` - Format code with Laravel Pint

## Contributing

1. Ensure all tests pass before submitting a PR
2. Follow the PSR-12 coding standard (enforced by Pint)
3. Write tests for new features
4. Update documentation as needed
5. Create PRs targeting the `dev` branch

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Author

Created by [Pere Coll](https://github.com/pcollcasellas)
