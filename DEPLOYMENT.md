# Deployment Guide - ZB Laravel SmilePay Package

This guide walks you through publishing your package to GitHub and Packagist.

## Prerequisites

- Git installed and configured
- GitHub account
- Packagist account (free at https://packagist.org)

## Step 1: Initialize Git Repository

```bash
cd "C:\Users\Greats Sys\Documents\GitHub\smilepay"

# Initialize git (if not already done)
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit: Laravel SmilePay package v1.0.0

- Standard Checkout integration
- Express Checkout for Ecocash, Innbucks, Omari, SmileCash, Cards
- Webhook handler with Laravel events
- Transaction management utilities
- Comprehensive documentation"
```

## Step 2: Create GitHub Repository

### Option A: Via GitHub Website

1. Go to https://github.com/new
2. Repository name: `zb-laravel-smilepay`
3. Description: `Laravel package for SmilePay Payment Gateway (ZB Bank Zimbabwe)`
4. Select: **Public** (required for Packagist)
5. **Do NOT** initialize with README (you already have one)
6. Click **Create repository**

### Option B: Via GitHub CLI

```bash
# Install GitHub CLI if needed: https://cli.github.com/

# Login to GitHub
gh auth login

# Create repository
gh repo create zb-laravel-smilepay --public --description "Laravel package for SmilePay Payment Gateway (ZB Bank Zimbabwe)" --source=.

# Push code
git push -u origin main
```

### Option C: Manual Setup

```bash
# Add remote
git remote add origin https://github.com/emmanuelsiziba/zb-laravel-smilepay.git

# Rename branch to main (if needed)
git branch -M main

# Push code
git push -u origin main
```

## Step 3: Create a Git Tag for v1.0.0

```bash
# Create annotated tag
git tag -a v1.0.0 -m "Release version 1.0.0

Initial release with:
- Multi-payment gateway support
- Standard and Express checkout
- Webhook integration
- Full documentation"

# Push tag to GitHub
git push origin v1.0.0
```

## Step 4: Submit to Packagist

### 4.1 Register on Packagist

1. Visit https://packagist.org/
2. Click **Sign in with GitHub**
3. Authorize Packagist to access your GitHub account

### 4.2 Submit Package

1. Click **Submit** in top navigation
2. Enter repository URL: `https://github.com/emmanuelsiziba/zb-laravel-smilepay`
3. Click **Check**
4. Review package information
5. Click **Submit**

### 4.3 Set Up Auto-Update Hook (IMPORTANT!)

**This is what you were warned about!**

After submitting your package:

1. Go to your package page: https://packagist.org/packages/emmanuelsiziba/zb-laravel-smilepay
2. Click **Settings** (or you'll see a warning banner)
3. Two options:

#### Option A: GitHub Service Hook (Recommended)

1. In Packagist, copy the webhook URL shown
2. Go to GitHub repository: https://github.com/emmanuelsiziba/zb-laravel-smilepay/settings/hooks
3. Click **Add webhook**
4. Paste the Packagist webhook URL
5. Content type: `application/json`
6. Select: **Just the push event**
7. Check: **Active**
8. Click **Add webhook**

#### Option B: GitHub OAuth Token

1. In Packagist package settings
2. Click **Enable GitHub Hook**
3. Follow OAuth authorization
4. Packagist will automatically sync with GitHub

**Verification:**
- Push a small change to GitHub
- Check if Packagist updates automatically (within minutes)
- If not, check webhook delivery status in GitHub settings

## Step 5: Add Repository Topics (SEO)

1. Go to https://github.com/emmanuelsiziba/zb-laravel-smilepay
2. Click the gear icon next to "About"
3. Add topics:
   - `laravel`
   - `payment-gateway`
   - `zimbabwe`
   - `ecocash`
   - `innbucks`
   - `smilepay`
   - `zb-bank`
   - `laravel-package`
   - `mobile-money`
4. Save changes

## Step 6: Create GitHub Release

1. Go to https://github.com/emmanuelsiziba/zb-laravel-smilepay/releases
2. Click **Draft a new release**
3. Choose tag: `v1.0.0`
4. Release title: `v1.0.0 - Initial Release`
5. Description:

```markdown
## SmilePay Laravel Package - First Release 🎉

A comprehensive Laravel package for integrating SmilePay Payment Gateway (ZB Bank Zimbabwe).

### Features

✅ **Multi-Payment Support**
- Ecocash (Mobile Money)
- Innbucks (Digital Wallet)
- Omari (Payment Platform)
- SmileCash (Digital Wallet)
- Visa/Mastercard (Credit/Debit Cards)

✅ **Integration Methods**
- Standard Checkout (Hosted Payment Page)
- Express Checkout (Direct API Integration)

✅ **Developer Features**
- Webhook handling with Laravel events
- Transaction status checking
- Payment cancellation
- Comprehensive logging
- Sandbox & Production support

### Installation

```bash
composer require emmanuelsiziba/zb-laravel-smilepay
```

### Documentation

- [README](https://github.com/emmanuelsiziba/zb-laravel-smilepay#readme)
- [Examples](https://github.com/emmanuelsiziba/zb-laravel-smilepay/blob/main/EXAMPLES.md)
- [Package Structure](https://github.com/emmanuelsiziba/zb-laravel-smilepay/blob/main/PACKAGE_STRUCTURE.md)

### Requirements

- PHP 8.0+
- Laravel 9.x, 10.x, or 11.x

### Credits

Developed by Emmanuel Siziba
```

6. Click **Publish release**

## Step 7: Verify Installation

Test that your package can be installed:

```bash
# Create a test Laravel project
laravel new test-app
cd test-app

# Install your package
composer require emmanuelsiziba/zb-laravel-smilepay

# Publish config
php artisan vendor:publish --tag=smilepay-config

# Check if it works
php artisan tinker
>>> app('smilepay')
```

## Step 8: Add Badges to README (Optional)

Your README already has:
- Latest Version badge
- Total Downloads badge
- License badge

You can add more:

```markdown
![PHP Version](https://img.shields.io/packagist/php-v/emmanuelsiziba/zb-laravel-smilepay)
![Build Status](https://img.shields.io/github/actions/workflow/status/emmanuelsiziba/zb-laravel-smilepay/tests.yml)
![GitHub Stars](https://img.shields.io/github/stars/emmanuelsiziba/zb-laravel-smilepay)
```

## Maintenance Commands

### Update Package After Changes

```bash
# Make your changes
git add .
git commit -m "Description of changes"
git push

# For new version
git tag -a v1.0.1 -m "Bug fixes and improvements"
git push origin v1.0.1

# Create GitHub release for the new tag
```

### Monitor Package

- **Packagist**: https://packagist.org/packages/emmanuelsiziba/zb-laravel-smilepay/stats
- **GitHub Insights**: https://github.com/emmanuelsiziba/zb-laravel-smilepay/pulse

## Troubleshooting

### Package Not Found

**Problem**: `composer require` returns "Package not found"

**Solution**:
1. Wait 1-5 minutes after Packagist submission
2. Run: `composer clear-cache`
3. Check package exists: https://packagist.org/packages/emmanuelsiziba/zb-laravel-smilepay

### Auto-Update Not Working

**Problem**: Packagist not updating when you push to GitHub

**Solutions**:
1. Check GitHub webhook deliveries (Settings > Webhooks)
2. Verify webhook URL is correct
3. Re-add webhook if needed
4. Manual update: Visit package page on Packagist and click "Update"

### Namespace Errors After Installation

**Problem**: Class not found errors

**Solution**:
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

## Next Steps

1. ✅ Set up GitHub Actions for automated testing (optional)
2. ✅ Create a documentation website (GitHub Pages, ReadTheDocs)
3. ✅ Add contribution guidelines (CONTRIBUTING.md)
4. ✅ Set up issue templates
5. ✅ Create a Discord/Slack community (optional)
6. ✅ Submit to Laravel News for visibility

## Quick Reference

| Task | Command |
|------|---------|
| Create tag | `git tag -a v1.0.0 -m "message"` |
| Push tag | `git push origin v1.0.0` |
| List tags | `git tag -l` |
| Delete tag | `git tag -d v1.0.0` |
| Clear composer cache | `composer clear-cache` |

---

**Package**: emmanuelsiziba/zb-laravel-smilepay
**Author**: Emmanuel Siziba
**License**: MIT
