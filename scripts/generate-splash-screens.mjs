#!/usr/bin/env node
/**
 * Generates iOS PWA splash screen images.
 * Run: node scripts/generate-splash-screens.mjs
 */

import sharp from 'sharp';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const iconPath = path.join(__dirname, '../public/images/icons/icon-512x512.png');
const outputDir = path.join(__dirname, '../public/images/splash');

// iPhone 13: 390x844 @3x = 1170x2532
// iPhone 13 mini: 375x812 @3x = 1080x2340
// iPhone 14/13 Pro Max: 428x926 @3x = 1284x2778
// iPhone 16: 393x852 @3x = 1179x2556
const SIZES = [
  { width: 1170, height: 2532, name: 'splash-1170x2532.png' },
  { width: 1080, height: 2340, name: 'splash-1080x2340.png' },
  { width: 1284, height: 2778, name: 'splash-1284x2778.png' },
  { width: 1179, height: 2556, name: 'splash-1179x2556.png' },
  { width: 750, height: 1334, name: 'splash-750x1334.png' }, // iPhone 8/SE
  { width: 1125, height: 2436, name: 'splash-1125x2436.png' }, // iPhone X
];

const BACKGROUND_COLOR = '#fdfcfa';

if (!fs.existsSync(iconPath)) {
  console.error('Icon not found:', iconPath);
  process.exit(1);
}

if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir, { recursive: true });
}

const icon = await sharp(iconPath).resize(256, 256).toBuffer();

for (const { width, height, name } of SIZES) {
  const outputPath = path.join(outputDir, name);
  await sharp({
    create: {
      width,
      height,
      channels: 3,
      background: BACKGROUND_COLOR,
    },
  })
    .composite([
      {
        input: icon,
        left: Math.floor((width - 256) / 2),
        top: Math.floor((height - 256) / 2),
      },
    ])
    .png()
    .toFile(outputPath);
  console.log('Generated:', name);
}
