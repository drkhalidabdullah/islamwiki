# IslamWiki Release Notes

This document provides an overview of all IslamWiki releases, from the initial version to the latest release.

## Current Release: v0.0.0.2

**Release Date**: September 6, 2025  
**Status**: Latest Release  
**Codename**: "Wiki Revolution"

### Overview
Major release featuring a complete wiki system overhaul with markdown editing, version control, and advanced search capabilities.

### Key Features
- Complete markdown-based wiki system
- Rich text editor with visual toolbar
- Wiki-style linking with `[[Page Name]]` syntax
- Article version control and history
- Advanced search with filters and suggestions
- Test content (Islam, Allah, Muslim articles)

[Read Full Release Notes](changelogs/v0.0.0.2.md)

---

## Release History

### v0.0.0.2 - "Wiki Revolution" (September 6, 2025)
**Status**: Latest Release

**Major Features:**
- Complete wiki system with markdown editing
- Rich text editor with visual toolbar
- Wiki linking system with smart detection
- Article version control and restoration
- Advanced search with full-text capabilities
- Professional wiki interface and navigation

**Technical Improvements:**
- Modular markdown parser system
- Enhanced database schema with version control
- Optimized search performance
- Improved code architecture and organization

[Full Changelog](changelogs/v0.0.0.2.md)

### v0.0.0.1 - "Fresh Start" (September 6, 2025)
**Status**: Previous Release

**Major Features:**
- Complete PHP platform rebuild (removed React)
- User authentication and role management
- Basic wiki system with article management
- Admin panel with comprehensive tools
- Database schema with user and content system

**Technical Features:**
- Secure password hashing and session management
- CSRF protection and input sanitization
- Responsive design and accessibility features
- Clean file structure with public/ directory

[Full Changelog](changelogs/v0.0.0.1.md)

---

## Version Numbering

IslamWiki uses semantic versioning with the format `MAJOR.MINOR.PATCH`:

- **MAJOR**: Breaking changes or major feature additions
- **MINOR**: New features that are backward compatible  
- **PATCH**: Bug fixes and minor improvements

### Development Phases

- **0.0.x**: Alpha releases - Core functionality and major features
- **0.1.x**: Beta releases - Feature completion and refinement
- **0.2.x**: Release candidate - Bug fixes and polish
- **1.0.0**: Stable release - Production ready

---

## Upcoming Releases

### v0.0.0.3 - "Global Reach" (Planned)
**Target Date**: TBD

**Planned Features:**
- Multi-language support and translation system
- Advanced user permissions and content moderation
- API endpoints for third-party integrations
- Enhanced mobile experience

### v0.1.0 - "Community Edition" (Planned)
**Target Date**: TBD

**Planned Features:**
- Community features and collaboration tools
- Advanced content moderation
- User-generated content system
- Social features and user interactions

### v1.0.0 - "Production Ready" (Planned)
**Target Date**: TBD

**Planned Features:**
- Production-ready stability
- Performance optimizations
- Security hardening
- Complete documentation and guides

---

## Installation & Upgrade

### Latest Release (v0.0.0.2)
```bash
git clone https://github.com/drkhalidabdullah/islamwiki.git
cd islamwiki
git checkout v0.0.0.2
mysql -u root -p < setup_database_fixed.sql
cd public
php -S 0.0.0.0:80
```

### Upgrade from Previous Version
1. Backup your current installation
2. Pull the latest changes: `git pull origin master`
3. Run database migrations if needed
4. Clear caches and restart your server

---

## Support & Documentation

- **Installation Guide**: [docs/guides/INSTALLATION.md](guides/INSTALLATION.md)
- **User Guide**: [docs/guides/USER_GUIDE.md](guides/USER_GUIDE.md)
- **API Documentation**: [docs/api/API_REFERENCE.md](api/API_REFERENCE.md)
- **Changelog**: [docs/changelogs/CHANGELOG.md](changelogs/CHANGELOG.md)

---

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details on how to contribute to IslamWiki.

---

**IslamWiki** - Empowering Islamic knowledge through technology
