# Online Cloud Deployment Guide
## Hospital Management System (HMS) - Apex Memorial Hospital

This project is Dockerized and pre-configured for free cloud hosting on **Render.com** and **Railway.app**.

---

## 🚀 Option A: Deploy to Render.com (100% Free Tier)

Render provides free hosting for Docker container services with automatic SSL (HTTPS).

### Step 1: Push Project to GitHub
1. Create a new repository on [GitHub](https://github.com/new) (e.g. `hospital-management-system`).
2. Open PowerShell in this project folder:
   ```powershell
   cd "C:\Users\ASUS\.gemini\antigravity\scratch\hospital-management-system"
   git remote add origin https://github.com/<YOUR-USERNAME>/hospital-management-system.git
   git branch -M main
   git push -u origin main
   ```

### Step 2: Deploy on Render
1. Sign up or log in at [Render.com](https://render.com).
2. Click **New +** in the top navigation and select **Web Service**.
3. Select **Build and deploy from a Git repository** and connect your `hospital-management-system` repository.
4. Render will automatically detect the `Dockerfile` and `render.yaml`.
5. Configuration summary:
   - **Environment**: `Docker`
   - **Instance Type**: `Free`
   - **Health Check Path**: `/index.php?page=login`
6. Click **Deploy Web Service**.
7. In ~2 minutes, your system will be live at:
   `https://hospital-management-system-xxxx.onrender.com`

---

## 🚂 Option B: Deploy to Railway.app

Railway provides instant container deployment with zero configuration.

### Step 1: Push Project to GitHub (Same as Step 1 above)

### Step 2: Deploy on Railway
1. Sign up or log in at [Railway.app](https://railway.app).
2. Click **New Project** &rarr; **Deploy from GitHub repo**.
3. Choose your `hospital-management-system` repository.
4. Railway will automatically detect `railway.json` and build the `Dockerfile`.
5. Once deployment completes, go to **Settings** &rarr; **Networking** &rarr; click **Generate Domain**.
6. Your live public URL will be ready (e.g. `https://hospital-management-system.up.railway.app`).

---

## ⚡ Option C: Instant Public Internet URL (No Cloud Account Required)

If you want to immediately share the application with anyone over the internet from your computer without signing up for cloud hosting:

1. Double-click [run-server.bat](file:///C:/Users/ASUS/.gemini/antigravity/scratch/hospital-management-system/run-server.bat) to start the local server on `http://localhost:8000`.
2. Open another command prompt and run:
   ```powershell
   winget install --id Cloudflare.cloudflared -e
   cloudflared tunnel --url http://localhost:8000
   ```
   *(Or using ngrok: `npx ngrok http 8000`)*
3. A public HTTPS link will be generated (e.g. `https://random-words.trycloudflare.com`) that anyone can open from anywhere in the world!

---

## 🔒 Cloud Production Security Best Practices

- **Database Persistence**: The container uses SQLite by default. On Render or Railway, to persist patient changes across container rebuilds, attach a persistent disk mounted to `/var/www/html/database.sqlite`.
- **Default Passwords**: After deploying online, log in as `admin` (`password123`) and update default passwords from the Admin profile screen.
- **Backups**: Access the **Backup & Recovery** tab to download snapshots to your local drive anytime.
