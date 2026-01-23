#!/usr/bin/env pwsh

<#
.SYNOPSIS
    Automated Git Add, Commit, and Push script
    
.DESCRIPTION
    This script automates the git workflow: add all changes, commit with a message, and push to remote
    
.PARAMETER Message
    The commit message
    
.EXAMPLE
    .\git-commit-push.ps1 -Message "Fix unit barang ruang_id issue"
    .\git-commit-push.ps1 "Fix unit barang ruang_id issue"
#>

param(
    [Parameter(Position = 0, Mandatory = $true, HelpMessage = "Commit message")]
    [string]$Message
)

# Set error action preference
$ErrorActionPreference = "Stop"

# Check if we're in a git repository
$gitDir = git rev-parse --git-dir 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Not a git repository!" -ForegroundColor Red
    exit 1
}

Write-Host "🚀 Git Workflow Started" -ForegroundColor Cyan
Write-Host "📝 Commit Message: $Message" -ForegroundColor Yellow
Write-Host ""

try {
    # Step 1: Git Add
    Write-Host "➕ Running: git add ." -ForegroundColor Green
    git add .
    if ($LASTEXITCODE -ne 0) {
        throw "git add failed"
    }
    Write-Host "✅ Changes staged successfully" -ForegroundColor Green
    Write-Host ""

    # Step 2: Git Commit
    Write-Host "💾 Running: git commit -m (message)" -ForegroundColor Green
    git commit -m $Message
    if ($LASTEXITCODE -ne 0) {
        throw "git commit failed"
    }
    Write-Host "✅ Commit created successfully" -ForegroundColor Green
    Write-Host ""

    # Step 3: Git Push to GitHub
    Write-Host "📤 Running: git push origin main" -ForegroundColor Green
    git push origin main
    if ($LASTEXITCODE -ne 0) {
        throw "git push failed"
    }
    Write-Host "✅ Push completed successfully" -ForegroundColor Green
    Write-Host ""

    Write-Host "🎉 All git operations completed successfully!" -ForegroundColor Cyan
    Write-Host ""
}
catch {
    Write-Host "❌ Error: $_" -ForegroundColor Red
    exit 1
}
