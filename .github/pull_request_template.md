# OpenBooks Pull Request Template

## Description
<!-- Provide a brief description of the changes in this PR -->

## Type of Change
<!-- Mark the relevant option with an [x] -->

- [ ] ✨ New feature (non-breaking change which adds functionality)
- [ ] 🐛 Bug fix (non-breaking change which fixes an issue)
- [ ] 📝 Documentation update
- [ ] ♻️ Code refactoring (no functional changes)
- [ ] ⚡️ Performance improvement
- [ ] ✅ Test addition or update
- [ ] 🔧 Configuration or build system change
- [ ] 💥 Breaking change (fix or feature that would cause existing functionality to not work as expected)

## Related Issues
<!-- Link to any related issues using "Fixes #123" or "Related to #123" -->

Fixes #

## Testing
<!-- Describe the testing you've done -->

- [ ] Unit tests added/updated and passing
- [ ] Feature tests added/updated and passing
- [ ] Manual testing performed
- [ ] Tested on local environment

### Test Configuration
- PHP Version:
- Laravel Version:
- Database:

## Screenshots (if applicable)
<!-- Add screenshots to help explain your changes, especially for UI changes -->

## Checklist
<!-- Mark completed items with [x] -->

- [ ] My code follows the project's style guidelines (PHP Pint + PHPStan Level 8)
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes
- [ ] Any dependent changes have been merged and published

## Domain-Specific Checklist
<!-- If your PR touches specific domains, please verify: -->

### If modifying Invoicing:
- [ ] Invoice status transitions handled correctly
- [ ] PDF generation tested
- [ ] Email delivery working

### If modifying Payments:
- [ ] Stripe integration tested
- [ ] Payment webhooks handling properly
- [ ] PCI compliance maintained (no card data stored)

### If modifying Authentication:
- [ ] Multi-tenancy isolation maintained
- [ ] Role-based access control working
- [ ] 2FA flows tested (if applicable)

## Deployment Notes
<!-- Any special considerations for deployment -->

- [ ] Requires database migrations
- [ ] Requires environment variable changes
- [ ] Requires composer install
- [ ] Requires npm install and build

## Additional Notes
<!-- Add any other context about the PR here -->
