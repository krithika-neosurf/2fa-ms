# merge.ps1
# -------------------------
# Usage: Run this inside your Git repository folder
# Requirements: Bitbucket API token with
#   - Repository: Read + Write
#   - Pull request: Write
# Username for API token auth: x-bitbucket-api-token-auth
# Password: Your API token

# === CONFIGURATION ===
$ApiToken = "ATCTT3xFfGN0psaCOl30wjNqJ2DzozW_44IhasuR8AyBUu8CS9ggtnH1Pge7CpCJ1p_F4U0PXq1uFRW32cMmTNZc1h9MIny_0bBkB52kiAalCDtRhVQigTeu5aFqAjc7xloNCeFA2vrS2uMRYsw9U4V2m8RAvd1NFH-wGXZtDSfa5-d3dl304OE=9A035BE9"
$Workspace = "neosurfiteam"       # Bitbucket workspace ID
$RepoSlug = "2fa-ms"             # Repository slug
$Branch = "staging"              # Branch to push
$TargetBranch = "develop"           # Branch to create PR into

# === GIT SETUP ===
# Make sure we are in a Git repository
if (-not (Test-Path ".git")) {
    Write-Host "Error: Not a Git repository!"
    exit
}

# Fetch latest remote info
git fetch origin

# Checkout branch
git checkout $Branch

# Pull remote changes to avoid non-fast-forward error
try {
    git pull origin $Branch
    Write-Host "Pulled latest changes from remote $Branch"
} catch {
    Write-Host "Warning: Pull failed. Resolve conflicts manually."
}

# Push branch
try {
    git push origin $Branch
    Write-Host "Branch '$Branch' pushed successfully!"
} catch {
    Write-Host "Push failed. Consider using 'git push --force' if you want to overwrite remote."
}

# === BITBUCKET API AUTH ===
$headers = @{
    Authorization = "Bearer $ApiToken"
    Accept        = "application/json"
}

# === CREATE PULL REQUEST ===
$body = @{
    title = "Auto PR from $Branch to $TargetBranch"
    source = @{ branch = @{ name = $Branch } }
    destination = @{ branch = @{ name = $TargetBranch } }
    description = "Created via merge.ps1 script"
} | ConvertTo-Json -Depth 5

try {
    $response = Invoke-RestMethod `
        -Uri "https://api.bitbucket.org/2.0/repositories/${Workspace}/${RepoSlug}/pullrequests" `
        -Method Post `
        -Headers $headers `
        -Body $body `
        -ContentType "application/json"

    Write-Host "Pull Request created successfully!"
    Write-Host "PR URL: " $response.links.html.href
	
	$prId = $response.id
	
} catch {
    Write-Host "Failed to create PR. Check token scopes, branch names, or existing PRs."
    Write-Host $_
	if ($_.Exception.Response) {
        $reader = New-Object IO.StreamReader(
            $_.Exception.Response.GetResponseStream()
        )
        $reader.ReadToEnd()
    }
    exit 1
}
Write-Host "PR ID: " $prId

# === TRIGGER AUTO-MERGE PIPELINE ===
$pipelineBody = @{
    target = @{
        type     = "pipeline_ref_target"
        ref_type = "branch"
        ref_name = $Branch
        selector = @{
            type    = "custom"
            pattern = "auto-merge-pr"
        }
    }
    variables = @(
        @{
            key   = "AUTO_MERGE_PR_ID"
            value = "$prId"
        }
    )
} | ConvertTo-Json -Depth 10

try {
    $pipelineResponse = Invoke-RestMethod `
        -Uri "https://api.bitbucket.org/2.0/repositories/${Workspace}/${RepoSlug}/pipelines/" `
        -Method Post `
        -Headers $headers `
        -Body $pipelineBody `
        -ContentType "application/json"

    Write-Host "Auto-merge pipeline triggered successfully!"
    Write-Host "Pipeline UUID: $($pipelineResponse.uuid)"
}
catch {
    Write-Host "Failed to trigger auto-merge pipeline."
    Write-Host $_
    exit 1
}
Read-Host "Press Enter to exit"

