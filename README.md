# serversideee
This is the respository for Server Side Assignment 

# How to Upload Files to GitHub Using VS Code

## Prerequisites
- Install **Git**: [Download Git](https://git-scm.com/downloads)
- Install **VS Code**: [Download VS Code](https://code.visualstudio.com/)
- Create a **GitHub account**: [Sign up on GitHub](https://github.com/)
- (Optional) Set up **GitHub CLI**: [GitHub CLI](https://cli.github.com/)

---

## Step 1: Initialize Git in Your Project
1. Open **VS Code** and navigate to your project folder.
2. Open the **Terminal** in VS Code (`Ctrl + ~` or `View` → `Terminal`).
3. Run the following command to initialize Git:
   ```sh
   git init
   ```

---

## Step 2: Connect to a GitHub Repository
1. Go to [GitHub](https://github.com/) and create a **new repository**.
2. Copy the **repository URL** (HTTPS recommended).
3. In VS Code terminal, run:
   ```sh
   git remote add origin <your-repo-URL>
   ```
   Example:
   ```sh
   git remote add origin https://github.com/your-username/your-repository.git
   ```

---

## Step 3: Add, Commit, and Push Files
1. **Check the status** of your files:
   ```sh
   git status
   ```
2. **Stage all files** for commit:
   ```sh
   git add .
   ```
3. **Commit the changes**:
   ```sh
   git commit -m "Initial commit"
   ```
4. **Push to GitHub**:
   ```sh
   git branch -M main
   git push -u origin main
   ```

---

## Step 4: Pull Latest Changes (If Needed)
If the repository already has changes, first pull the latest updates:
```sh
git pull origin main --rebase
```
If you have uncommitted changes, either **commit them** or **stash them** before pulling.

---

## Future Updates
Whenever you make changes and want to update GitHub:
```sh
git add .
git commit -m "Updated files"
git push origin main
```

---

## Troubleshooting
- **Error: rejected push** → Run:
  ```sh
  git pull origin main --rebase
  ```
- **Uncommitted changes error** → Commit or stash changes:
  ```sh
  git stash
  git pull origin main --rebase
  git stash pop
  ```



