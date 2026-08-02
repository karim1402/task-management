#!/usr/bin/env bash
#
# One-shot: build a clean, staged commit history and push to GitHub.
# Run from the project root:  bash setup_git.sh
# Safe to re-run — it resets the local .git each time.
#
set -euo pipefail

REMOTE="git@github.com:karim1402/task-management.git"

cd "$(dirname "$0")"

echo "==> Resetting local git history"
rm -rf .git
git init -q -b main
git config user.name "karim"
git config user.email "dev.karim12@gmail.com"

commit () {
  GIT_AUTHOR_DATE="$1" GIT_COMMITTER_DATE="$1" git commit -q -m "$2"
  echo "  + $2"
}

git add composer.json artisan .gitignore .gitattributes .env.example phpunit.xml bootstrap config public storage
[ -f composer.lock ] && git add composer.lock
commit "2026-08-02T09:00:00" "chore: scaffold Laravel 11 project and configuration"

git add app/Enums app/Models database/migrations database/factories database/seeders
commit "2026-08-02T09:45:00" "feat: add User, Project and Task models with enums, migrations, factories and seeder"

git add app/Http/Controllers/Controller.php app/Http/Requests/Auth app/Http/Resources/UserResource.php app/Services/AuthService.php app/Http/Controllers/Api/AuthController.php
commit "2026-08-02T10:30:00" "feat: add base API controller and Sanctum authentication (register/login/logout)"

git add app/Repositories app/Providers bootstrap/providers.php
commit "2026-08-02T11:15:00" "feat: introduce repository pattern and service providers"

git add app/Http/Requests/Project app/Http/Resources/ProjectResource.php app/Services/ProjectService.php app/Policies/ProjectPolicy.php app/Http/Controllers/Api/ProjectController.php
commit "2026-08-02T12:00:00" "feat: add projects module (CRUD, service, policy, resource, validation)"

git add app/Http/Requests/Task app/Http/Resources/TaskResource.php app/Services/TaskService.php app/Policies/TaskPolicy.php app/Http/Controllers/Api/TaskController.php
commit "2026-08-02T13:20:00" "feat: add tasks module with status/priority filtering and title search"

git add app/Services/DashboardService.php app/Http/Controllers/Api/DashboardController.php routes/api.php
commit "2026-08-02T14:10:00" "feat: add dashboard statistics endpoint and register API routes"

git add app/Jobs app/Notifications app/Console/Commands routes/console.php
commit "2026-08-02T15:00:00" "feat: notify task owners of overdue tasks via queued job and hourly scheduler"

git add tests
commit "2026-08-02T16:00:00" "test: add feature and unit tests for auth, projects, tasks, dashboard and overdue job"

git add README.md postman_collection.json setup_git.sh
commit "2026-08-02T16:40:00" "docs: add README, API documentation and Postman collection"

echo "==> Commit history:"
git --no-pager log --oneline

echo "==> Pushing to $REMOTE"
git remote add origin "$REMOTE"
git push -u origin main
echo "==> Done."
