# Assignment #1: Building a Personal Website with HTML & CSS

## Description
Create a static personal website using **HTML** and **CSS** that reflects your identity and portfolio. This website should have a **responsive**, **attractive** design that meets modern web quality standards.

## Goals
By the end of this assignment, students are expected to be able to:
- Build static web pages using semantic **HTML** and external **CSS**.
- Apply modern layout techniques using **flexbox** or **grid**.
- Create **responsive** designs that work across different screen sizes.
- Use **Git** in a structured way: creating branches, writing descriptive commit messages, and merging changes via Pull Requests.
- Publish a website to the internet using a free **hosting** service.
- Ensure code quality through **validation** and basic **accessibility** standards.

## Specifications

### 1. Page Structure (At Least 3 Pages)
- **Home Page (index.html)**: Contains a brief introduction, profile picture, and self-description.
- **Portfolio Page (portfolio.html)**: Displays projects or past experiences.
- **Contact Page (contact.html)**: Contains a contact form (does not need to be functional, design only).

### 2. Required HTML Elements
- Use **header, nav, main, footer** semantically.
- Navigation between pages must work.
- Every page must include the following meta tags inside `<head>`:
  ```html
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Brief description of this page">
  ```
- Include a **favicon** (the small icon that appears in the browser tab).

### 3. Style and Appearance (CSS)
- Use **external CSS** (separate `.css` file).
- Utilize **flexbox or grid** for layout.
- Apply consistent colors, fonts, and spacing.
- Use **Google Fonts** or icons from **FontAwesome**.
- **Responsive** (use media queries at least for mobile and desktop views).
- Hover effects or simple **animations** using CSS.
- Do not use **inline styles** (`style="..."`) — all styles must be in the external CSS file.

### 4. Code Quality
- File and folder names must use **lowercase letters** and **no spaces** (use `-` as separator, e.g. `about-me.html`, `style/main.css`).
- HTML and CSS must **pass validation** using:
  - [W3C HTML Validator](https://validator.w3.org/)
  - [W3C CSS Validator](https://jigsaw.w3.org/css-validator/)
- Include screenshots of the validation results in `REPORT.md`.

### 5. Accessibility (a11y)
- All `<img>` elements must have a descriptive `alt` attribute.
- Text and background color contrast must be sufficient (minimum ratio of 4.5:1 for normal text).
- All navigation must be accessible using the keyboard (Tab key).

### 6. Deployment (Required)
- **Host the website online** using **GitHub Pages**, Netlify, or Vercel.
- Include the link to the hosted website in `REPORT.md`.

### 7. Bonus (Optional)
- Dark mode toggle.
- **Lighthouse score ≥ 80** in the **Performance** and **Accessibility** categories (include a screenshot in `REPORT.md`).

## Submission via GitHub Classroom

> **Individual assignment.** Each student works on their own repository provided by GitHub Classroom.

### 0. Prerequisite: SSH Setup on GitHub
Before you start, make sure you have configured an SSH key on GitHub so you can `git push` without entering your password every time.

1. Generate an SSH key (if you don't have one):
   ```bash
   ssh-keygen -t ed25519 -C "your@email.com"
   ```
2. Copy the contents of the public key (`~/.ssh/id_ed25519.pub`) to **GitHub → Settings → SSH and GPG keys → New SSH key**.
3. Test the connection:
   ```bash
   ssh -T git@github.com
   ```
   If successful, you will see: `Hi <username>! You've successfully authenticated...`
4. Clone the repository using the **SSH** URL (not HTTPS):
   ```bash
   git clone git@github.com:....git
   ```

### 1. GitHub Repository
- Ensure all project files are stored and **submitted** in the repository provided through **GitHub Classroom**.
- The directory structure must be neat and easy to understand.
- It is recommended to **create a branch** (e.g. `dev` or `feature/feature-name`) and merge it into `main` via a **Pull Request** on GitHub.
- Make **regular** and descriptive **commits** — **at least 5 meaningful commits** (not just one large commit at the end).

#### Commit Message Format
Use the **Conventional Commits** format below:

| Prefix | Used for |
|--------|----------|
| `feat:` | adding a new feature or page |
| `fix:` | fixing a bug or visual issue |
| `style:` | CSS changes without affecting functionality |
| `docs:` | changes to `REPORT.md` or other documentation |
| `chore:` | general tasks (adding images, favicon, etc.) |

Examples of good commit messages:
```
feat: add portfolio page with grid layout
fix: fix navigation not responsive on mobile
style: adjust header color and typography
docs: add W3C validation screenshots to report
```

### 2. Report in Markdown Format (`REPORT.md`)
Create a `REPORT.md` file in the repository containing:
- **Project description** (purpose, main features, technologies used).
- **Project folder and file structure**.
- **Link to the hosted website** (required).
- **Screenshot proving SSH is configured** (output of the `ssh -T git@github.com` command).
- **Screenshots of W3C HTML and CSS validation results**.
- **Lighthouse screenshot** (if completing the bonus).
- The report format should be clear and readable.

## Submission Deadline
- Friday, **May 8, 2026** at 23.59 WIB.

## Assessment Criteria

| Criteria | Weight | Sub-criteria |
|----------|--------|--------------|
| **HTML Structure & Completeness** | 25% | Semantic tags, meta tags, favicon, page structure |
| **CSS Design & Neatness** | 25% | Consistent colors/fonts, proper flexbox/grid usage, no inline styles |
| **Responsiveness** | 15% | Layout works on mobile and desktop, appropriate media queries |
| **Navigation & User Experience** | 15% | Smooth page navigation, keyboard accessibility, `alt` on images |
| **Deployment & Code Quality** | 20% | Website successfully hosted, passes W3C validation, consistent file naming |
| **Bonus: REPORT.md** | +10 points | Very complete and well-structured documentation |
| **Bonus: Lighthouse ≥ 80** | +10 points | Performance and Accessibility score ≥ 80 |

## References

### HTML & CSS
| Resource | Topic |
|----------|-------|
| [MDN Web Docs — HTML](https://developer.mozilla.org/en-US/docs/Web/HTML) | Complete reference for HTML elements and attributes |
| [MDN Web Docs — CSS](https://developer.mozilla.org/en-US/docs/Web/CSS) | Complete reference for CSS properties |
| [CSS-Tricks: A Guide to Flexbox](https://css-tricks.com/snippets/css/a-guide-to-flexbox/) | Visual guide to flexbox |
| [CSS-Tricks: A Complete Guide to Grid](https://css-tricks.com/snippets/css/complete-guide-grid/) | Visual guide to CSS grid |

### Git & GitHub
| Resource | Topic |
|----------|-------|
| [Pro Git Book](https://git-scm.com/book/en/v2) | Free and comprehensive Git book |
| [GitHub Docs](https://docs.github.com) | Official GitHub documentation |
| [Learn Git Branching](https://learngitbranching.js.org/) | Interactive visual Git practice |
| [Conventional Commits](https://www.conventionalcommits.org/) | Commit message format specification |

#### Videos: SSH Setup on GitHub
| Video | Channel | Description |
|-------|---------|-------------|
| [Generating a new SSH key and adding it to the ssh-agent](https://www.youtube.com/watch?v=X40b9x9BFGo) | GitHub | Official SSH key tutorial for GitHub |
| [SSH GitHub Setup (Windows, Mac, Linux)](https://www.youtube.com/watch?v=snCP3c7wXw0) | Fireship | Short and concise, covers all OS |

#### Videos: Working with Git Effectively
| Video | Channel | Description |
|-------|---------|-------------|
| [Git in 100 Seconds](https://www.youtube.com/watch?v=hwP7WQkmECE) | Fireship | Core Git concepts in 100 seconds |
| [13 Advanced (but useful) Git Techniques and Shortcuts](https://www.youtube.com/watch?v=ecK3EnyGD8o) | Fireship | Practical everyday tips |
| [Git and GitHub for Beginners — Crash Course](https://www.youtube.com/watch?v=RGOj5yH7evk) | freeCodeCamp | Complete tutorial for beginners |
| [Git Branches Tutorial](https://www.youtube.com/watch?v=e2IbNHi4uCI) | freeCodeCamp | In-depth guide to branching |

### Deployment
| Resource | Topic |
|----------|-------|
| [GitHub Pages Docs](https://pages.github.com/) | How to host via GitHub Pages |
| [Netlify Docs](https://docs.netlify.com/) | How to host via Netlify |
| [Vercel Docs](https://vercel.com/docs) | How to host via Vercel |

### Accessibility & Quality
| Resource | Topic |
|----------|-------|
| [web.dev — Accessibility](https://web.dev/accessibility) | Accessibility guide by Google |
| [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/) | Check color contrast ratio |
| [W3C HTML Validator](https://validator.w3.org/) | HTML validation |
| [W3C CSS Validator](https://jigsaw.w3.org/css-validator/) | CSS validation |

### Video Tutorials
| Channel | Topic |
|---------|-------|
| [Kevin Powell](https://www.youtube.com/@KevinPowell) | In-depth CSS: flexbox, grid, responsiveness |
| [Traversy Media](https://www.youtube.com/@TraversyMedia) | HTML, CSS, and general web development tutorials |
| [Web Dev Simplified](https://www.youtube.com/@WebDevSimplified) | Web concepts explained concisely and clearly |

## Example Personal Websites
Here are some examples of personal websites and screenshots that can be used as inspiration:
- [Brittany Chiang](https://brittanychiang.com/)
- [Sindre Sorhus](https://sindresorhus.com/)
- [Benedikt Deicke](https://benediktdeicke.com/)


![](./images/personal-web-sample2.jpeg)
![](./images/personal-web-sample1.png)

## Happy Coding! 🚀
Take this opportunity to express your creativity in building an attractive and professional personal website. Feel free to experiment with design and additional features! If you encounter any difficulties, discuss them with your peers or ask in class.

**Good luck and happy coding!** 💪😊
