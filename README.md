# AI Category Assigner for WordPress

Automatically assign categories to your WordPress posts using AI (OpenAI or Pollinations.ai). This plugin helps you categorize your content efficiently using the most cost-effective AI models.

![WordPress version](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![PHP version](https://img.shields.io/badge/PHP-7.2%2B-purple)
![License](https://img.shields.io/badge/License-GPLv2-green)
![GitHub stars](https://img.shields.io/github/stars/khrieto/ai-category-assigner?style=social)

## 🚀 Features

- **Multiple AI Models Support:**
  - GPT-4o Mini (OpenAI) – Fast & Cost-Effective
  - GPT-4.1 Mini (OpenAI) – Latest Mini Model
  - Gemini Fast (Google Gemini 2.5 Flash Lite via Pollinations.ai) – Fast & Free Credits

- **Smart Assignment Options:**
  - Process all posts (replaces existing categories)
  - Process only uncategorized posts (skips posts with categories)

- **Batch Processing:** Processes posts in batches of 10 to avoid API limits
- **Case-Insensitive Category Matching:** Matches categories regardless of case
- **Progress Tracking:** Real-time progress bar and batch information
- **Visual Feedback:** See which categories were assigned to each post

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- API key from either:
  - [OpenAI](https://platform.openai.com/api-keys) (for GPT models)
  - [Pollinations.ai](https://enter.pollinations.ai) (for Gemini Fast model)

## 🔧 Installation

1. Download the plugin ZIP from GitHub.
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin.
3. Upload the ZIP and activate.
4. Go to Tools → AI Category Assigner to configure.
5. Enter your API key and target categories, then save.
6. Click "Load All Posts" and then "Assign Categories".
