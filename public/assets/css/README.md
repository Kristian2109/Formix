# CSS Organization

This project uses a modular CSS approach to organize styles based on their purpose and usage.

## Directory Structure

```
css/
├── base/               # Base styles and variables
│   ├── variables.css   # Color variables and theme settings
│   ├── reset.css       # Base reset and common elements
│   └── buttons.css     # Button styles
├── templates/          # Template-specific styles
│   ├── header.css      # Navigation and header styles
│   └── footer.css      # Footer styles
├── pages/              # Page-specific styles
│   ├── home.css        # Homepage specific styles
│   └── forms.css       # Styles for forms (login, register, etc.)
└── main.css            # Main CSS file that imports base and template styles
```

## How It Works

1. The `main.css` file imports all base and template styles that are needed across the site.
2. Page-specific CSS files are loaded dynamically by the header.php file based on the current page.

## Adding a New Page

When adding a new page:

1. Create a CSS file in the `pages/` directory if needed.
2. Update the switch statement in `header.php` to include your new page.

## Modifying Styles

- To change global variables (colors, etc.): Edit `base/variables.css`
- To modify header/footer: Edit respective files in `templates/`
- To change page-specific styles: Edit appropriate file in `pages/` 