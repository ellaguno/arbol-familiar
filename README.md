# Mi Familia

**Version:** 2.0.0

Generic open-source genealogy platform for documenting, preserving, and sharing family history. Built with modern web technologies and designed for collaborative genealogy work.

## Project Information

- **Developer:** Eduardo Llaguno Velasco ([eduardo@llaguno.com](mailto:eduardo@llaguno.com))
- **License:** MIT
- **Repository:** [https://github.com/ellaguno/arbol-familiar](https://github.com/ellaguno/arbol-familiar)

## Tech Stack

- **Backend:** Laravel 10.x, PHP 8.1+
- **Database:** MySQL 5.7+
- **Frontend:** Tailwind CSS, Alpine.js
- **Visualization:** D3.js (interactive family tree)
- **Build Tool:** Vite

## Features

- **Person Management**: Full registration of individuals with personal data, birth/death dates, places, and heritage information.
- **Family Management**: Linking couples and children, marriage dates, and relationship statuses.
- **Family Tree**: Interactive family tree visualization powered by D3.js.
- **GEDCOM Import/Export**: Compatible with the GEDCOM 5.5.1 standard for genealogical data exchange.
- **Advanced Search**: Filters by name, surname, gender, dates, birthplace, and heritage.
- **Admin Panel**: User management, statistical reports, and system configuration.
- **Multimedia**: Photo and document gallery associated with persons and families.
- **Messaging**: Internal messaging system between users.
- **Heritage Tracking**: Record regions and places of family origin.
- **Privacy Controls**: Multi-level privacy settings (private, family, community, public).
- **Minor Protection**: Automatic protection of information for underage individuals.
- **Data Auditing**: Built-in tools to detect and fix genealogical data inconsistencies.
- **Internationalization**: Multi-language support (Spanish and English).

## Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js >= 16
- NPM >= 8

## Installation

1. Clone the repository:
```bash
git clone https://github.com/ellaguno/arbol-familiar.git
cd arbol-familiar
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Copy configuration file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mi_familia
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

7. Run migrations:
```bash
php artisan migrate
```

8. (Optional) Seed test data:
```bash
php artisan db:seed
```

9. Create storage symlink:
```bash
php artisan storage:link
```

10. Build assets:
```bash
npm run build
```

11. Start development server:
```bash
php artisan serve
```

## Development

For local development with hot-reload:

```bash
# Terminal 1 - PHP Server
php artisan serve

# Terminal 2 - Vite (assets)
npm run dev
```

## Project Structure

```
app/
├── Http/Controllers/     # Controllers
│   ├── Admin/           # Administration controllers
│   └── Auth/            # Authentication controllers
├── Models/              # Eloquent Models
└── Services/            # Services (GEDCOM parser/exporter)

resources/
├── views/               # Blade Views
│   ├── admin/          # Administration views
│   ├── components/     # Reusable components
│   ├── persons/        # Person views
│   └── families/       # Family views
├── css/                # CSS Styles
└── js/                 # JavaScript

database/
├── migrations/         # Database migrations
└── seeders/           # Data seeders
```

## Contributing

Contributions are welcome. Please open an issue or submit a pull request at [https://github.com/ellaguno/arbol-familiar](https://github.com/ellaguno/arbol-familiar).

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

Copyright (c) 2024-2026 Eduardo Llaguno Velasco
