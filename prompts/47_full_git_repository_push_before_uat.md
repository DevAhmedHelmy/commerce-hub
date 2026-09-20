Claude Prompt — Complete Git Baseline, Commit & Full Repository Push Before UAT
Prompt Number
47
Purpose
Prepare and push the COMPLETE project repository to Git before manual UAT begins.
This is the ONE authoritative Git prompt for this checkpoint.
Goals:
1. Inspect the entire repository state.
2. Commit all intentional project work that belongs in Git.
3. Preserve unrelated/pre-existing local user changes unless clearly intended for this checkpoint.
4. Push the base branch (main or master).
5. Push the current development/feature branch.
6. Push all other intentional local branches.
7. Push all intentional tags.
8. Create and push a UAT baseline tag.
9. Verify the remote contains the intended repository history.
10. Do NOT implement new application features.
Repository root:
the current project repository.
Laravel application root:
src/
1. Inspect Git Completely
From repository root run:
git status --short
git branch --show-current
git branch -vv
git remote -v
git log --oneline --decorate --graph --all -40
git tag --list
Also inspect:
git diff --stat
git diff
If anything is staged:
git diff --cached --stat
git diff --cached
Report:
- current branch
- base branch (main or master)
- all local branches
- tracked/untracked files
- staged files
- current remote(s)
- latest commits
- existing tags
Do NOT assume branch names.
2. Detect Base Branch
Determine whether the project base branch is:
main
or:
master
Use actual Git history.
Do NOT rename it in this prompt.
The goal is only to ensure it is safely pushed.
3. Verify Remote
Expected remote is normally:
origin
Verify it exists.
If no remote exists:
STOP and return:
FULL GIT PUSH BLOCKED — NO REMOTE CONFIGURED
Do NOT invent or replace a remote URL.
If remote exists, report its fetch/push URL.
4. Protect Secrets and Runtime Files
Before staging anything, verify .gitignore.
NEVER commit:
.env
.env.* containing secrets
vendor/
node_modules/
storage/logs/
runtime/cache/session files
database dumps containing real data
API keys
OTP secrets
passwords
private credentials
IDE temp files
OS temp files
compiled local-only artifacts that should be ignored
Do not expose secret values in output.
If a secret is already staged:
UNSTAGE it safely before commit.
Do not delete the local file.
5. Existing Pre-Session User Changes
Previously reported local changes included:
README.md
src/config/session.php
src/app/Services/OtpService.php
Inspect them.
Do NOT automatically commit/revert/reset them merely because they are modified.
Determine whether they are:
A. intentional current project changes that the user clearly wants in this repository baseline
or
B. pre-existing unrelated/user-local changes
If ambiguous:
leave them untouched and report them separately.
Do not overwrite unknown user work.
6. Include Intentional Project Artifacts
The Git baseline SHOULD include intentional project artifacts such as:
src/
specs/
prompts/
.claude/
.specify/
CLAUDE.md
.gitignore
deployment/docs files
provided they are intentional project source/config/documentation files and contain no secrets.
This includes prompt history and project-specific Claude skills if the repository is intentionally using them as project artifacts.
Do NOT exclude them merely because they are documentation/tooling.
7. Verify Latest Landing/PWA Work
Before the UAT baseline, verify the latest intended Landing/PWA work is committed.
Current authoritative Landing implementation is the latest merged Landing/PWA/backend-controlled prompt in the repository.
Check actual commit history and files.
If valid project work remains uncommitted:
- review it
- verify it belongs to the current project
- stage it carefully
- commit it before push
Do not duplicate or rerun implementation.
8. Final Application Green Gate
Before committing/pushing the baseline, from:
cd src
run:
php artisan migrate:status
php artisan test
composer check-platform-reqs
npm run build
Requirements:
- migrations valid
- tests green
- PHP 8.2 compatibility green
- frontend build green
If a critical check fails:
STOP.
Return:
FULL GIT PUSH BLOCKED — PROJECT NOT GREEN
Do NOT push a knowingly broken UAT baseline.
Return to repo root afterward.
9. Stage Intentional Repository Work
From repository root:
carefully stage only intentional project files.
Preferred workflow:
git status --short
Then stage reviewed files.
If essentially all non-secret project files are intentional:
git add -A
is allowed ONLY AFTER verifying ignored/secrets/runtime files are excluded and any unrelated local user changes are intentionally handled.
Otherwise stage paths selectively.
After staging:
git diff --cached --stat
git diff --cached
Review the entire staged scope.
If unrelated or secret files appear:
unstage them before commit.
10. Create Final Pre-UAT Commit If Needed
If there are intentional uncommitted project changes, create ONE clean checkpoint commit.
Suggested message:
chore: prepare complete project baseline for UAT
If the staged scope is specifically feature work, use a more accurate conventional commit message.
Do not create an empty commit if nothing needs committing.
After commit:
git status --short
git log --oneline --decorate -10
11. Push Base Branch
Ensure the base branch exists locally.
Push it safely.
Example:
git push -u origin main
or:
git push -u origin master
Use the actual base branch.
Do NOT:
git push --force
git push --force-with-lease
If the remote rejects due to divergent/unrelated history:
STOP.
Do not overwrite remote history automatically.
Report the conflict.
12. Push All Intentional Local Branches
After verifying branch list:
push all local branches safely:
git push --all origin
This is intended to publish:
- base branch
- current feature branch
- other intentional local branches
Do NOT use:
git push --mirror
Do NOT delete remote branches.
Do NOT force push.
13. Ensure Current Branch Upstream
Verify:
git branch -vv
If current development branch has no upstream:
git push -u origin <current-branch>
If already tracked, no extra action needed.
14. Push Existing Tags
Push all intentional local tags:
git push --tags origin
If a tag conflict occurs:
STOP for that tag.
Do not overwrite remote tags automatically.
15. Create UAT Baseline Tag
After branch pushes succeed, create an annotated tag on the CURRENT tested commit.
Preferred:
uat-baseline-2026-09-20
If it already exists:
use the next non-conflicting suffix, e.g.:
uat-baseline-2026-09-20-2
Create:
git tag -a <tag-name> -m "UAT baseline before manual testing"
Push:
git push origin <tag-name>
No force.
16. Verify Remote Repository
Verify remote refs:
git ls-remote --heads origin
git ls-remote --tags origin
Also run:
git branch -vv
git log --oneline --decorate --graph --all -20
git tag --list "uat-baseline*"
git status --short
Confirm:
- base branch exists on remote
- current development branch exists on remote
- intended local branches exist on remote
- existing intentional tags exist on remote
- UAT baseline tag exists on remote
- tag points to intended tested commit
17. Working Tree Clarification
Remember:
Git push sends COMMITS, not uncommitted files.
Any file still shown by:
git status --short
is NOT backed up remotely unless committed.
In the final report clearly separate:
Successfully pushed
Committed repository history.
Still local only
Any intentionally preserved uncommitted user changes.
Do not claim they were pushed.
18. No Feature Work
During this Git prompt do NOT:
- alter business rules
- change UI
- fix random UAT issues
- redesign Landing
- modify database schema
- add new features
- start hardening work
Only fix something if required to safely complete the Git baseline itself.
19. Safety Rules
Never run:
git reset --hard
git clean -fd
git push --force
git push --mirror
Do not rewrite history.
Do not delete branches.
Do not delete tags.
Do not discard user changes.
20. Final Report
Return:
1. repository root
2. remote name/URL
3. detected base branch
4. current development branch
5. all local branches
6. final green test result
7. total test/assertion result
8. migration status
9. build result
10. PHP platform result
11. files committed in this run
12. commit hash/message created in this run
13. base branch push result
14. all-branches push result
15. tags push result
16. UAT baseline tag
17. UAT tag commit hash
18. remote branch verification
19. remote tag verification
20. files intentionally left uncommitted/local only
21. final git status --short
End with:
FULL GIT REPOSITORY PUSHED — READY FOR UAT
only when the intended branches and UAT baseline tag are verified on the remote.