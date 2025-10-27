#!/bin/bash
echo "Creating production-ready ZIP archive..."

# Create ZIP excluding unnecessary files
zip -r consultingg-production-ready.zip \
  backend/dist/ \
  backend/src/ \
  backend/prisma/ \
  backend/package.json \
  backend/package-lock.json \
  backend/tsconfig.json \
  backend/.env.production \
  backend/BUILD_COMPLETE.md \
  dist/ \
  public_html.htaccess \
  .env.production \
  BACKEND_BUILD_SUCCESS.md \
  DEPLOYMENT_CHECKLIST.md \
  DEPLOYMENT_READY.md \
  FIXES_SUMMARY.md \
  PRODUCTION_DEPLOYMENT_FIXED.md \
  QUICK_DEPLOYMENT_GUIDE.md \
  README_DEPLOYMENT.md \
  FILES_MODIFIED.md \
  -q

echo "✅ ZIP created: consultingg-production-ready.zip"
ls -lh consultingg-production-ready.zip
