# AI Category Assigner for WordPress

Automatically assign categories to your WordPress posts using AI (OpenAI or Pollinations.ai). This plugin helps you categorize your content efficiently using the most cost-effective AI models.

![WordPress version](https://img.shields.io/badge/WordPress-5.0%2B-blue)
![PHP version](https://img.shields.io/badge/PHP-7.2%2B-purple)
![License](https://img.shields.io/badge/License-GPLv2-green)
![GitHub stars](https://img.shields.io/github/stars/khrieto/ai-category-assigner?style=social)

## 📥 Download

### For WordPress Users
**[⬇️ Download AI.Category.Assigner.zip](https://github.com/khrieto/ai-category-assigner/releases/download/v2.0/AI.Category.Assigner.zip)**
- Clean plugin file (ready to install)
- Size: ~7KB
- Upload via WordPress Admin → Plugins → Add New → Upload Plugin

### For Developers
- [Full Source Code (ZIP)](https://github.com/khrieto/ai-category-assigner/archive/refs/tags/v2.0.zip)
- [Browse Repository](https://github.com/khrieto/ai-category-assigner)

---

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

## ⚙️ Configuration

### 1. Access the Plugin
- Go to **Tools → AI Category Assigner** in your WordPress admin menu

### 2. Enter API Keys
- **OpenAI API Key:** Required if using GPT-4o Mini or GPT-4.1 Mini
- **Pollinations.ai API Key:** Required if using Gemini Fast model

### 3. Select AI Model
Choose your preferred model:
- **GPT-4o Mini:** OpenAI's cost-effective model
- **GPT-4.1 Mini:** OpenAI's latest mini model
- **Gemini Fast:** Google's Gemini 2.5 Flash Lite via Pollinations

> ⚠️ **Important:** Make sure you enter the correct API key for your chosen model!

### 4. Set Assignment Mode
Choose how you want to assign categories:
- **All posts:** Process every post, replacing existing categories
- **Only uncategorized posts:** Only assign categories to posts with no categories

### 5. Define Target Categories
Enter your categories as a comma-separated list. Example: Technology, Business, Health, Lifestyle, Education, Travel, Food, Sports

### 6. Save Settings
Click "Save Settings" to store your configuration.

## 📝 How to Use

### Step 1: Load Posts
Click the **"Load All Posts"** button to fetch all your published posts.

### Step 2: Start Categorization
Click **"Assign Categories to All Posts"** to begin the AI categorization process.

### Step 3: Monitor Progress
- Watch the progress bar as posts are processed in batches of 10
- Each post gets updated with its AI-assigned category

### Step 4: Review Results
- Successfully categorized posts show a green category badge
- Failed posts show an error message in red

## ❓ Frequently Asked Questions

### Do I need both API keys?
No, only the key corresponding to your chosen model.

### What happens to existing categories?
They are replaced. Use "Only uncategorized posts" mode to skip already categorized posts.

### Can I undo changes?
No, changes are permanent. Always back up your database before processing large batches.

### Why are posts processed in batches?
To avoid hitting API rate limits and to prevent server timeouts.

### What if the AI suggests a category that doesn't exist?
The plugin matches the suggested category to your existing WordPress categories. If no match is found, the post is marked with an error.

## 🐛 Troubleshooting

**"API key not set" error**
- Ensure you've saved the correct API key for your selected model

**"Category not found" error**
- Make sure the category exists in WordPress
- Check that the category name is in your target categories list

**No posts loading**
- Verify you have published posts

## 💡 Tips for Best Results

1. Use clear category names that are distinct from each other
2. Keep your category list manageable (5-20 categories works best)
3. Test with a small batch first before processing all posts
4. The "uncategorized only" mode is great for new posts

## 📊 Performance

- Processes 10 posts per batch
- 500ms delay between batches to respect API limits
- Real-time progress tracking

## 🤝 Support

GitHub: [https://github.com/khrieto/ai-category-assigner](https://github.com/khrieto/ai-category-assigner)

## 📄 License

GPL v2 or later

## 🙏 Credits

- OpenAI for GPT models
- Pollinations.ai for Gemini Fast API access
- Google for Gemini AI technology

---

**Version:** 2.0  
**Last Updated:** March 2026
