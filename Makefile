# =============================================================================
# ROI Invoicing — server operations
#
# First-time setup:  make setup
# Start / stop:      make up  /  make down
# =============================================================================

.ONESHELL:
SHELL       := /bin/bash
.SHELLFLAGS := -euo pipefail -c

COMPOSE := docker compose

.DEFAULT_GOAL := help

# Terminal colours – degrade silently when not in a real TTY
BOLD   := $(shell tput bold   2>/dev/null || printf '')
GREEN  := $(shell tput setaf 2 2>/dev/null || printf '')
YELLOW := $(shell tput setaf 3 2>/dev/null || printf '')
CYAN   := $(shell tput setaf 6 2>/dev/null || printf '')
RED    := $(shell tput setaf 1 2>/dev/null || printf '')
RESET  := $(shell tput sgr0   2>/dev/null || printf '')

.PHONY: help setup up down restart build logs status shell artisan migrate destroy

# =============================================================================
# help
# =============================================================================
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
	  | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(CYAN)%-10s$(RESET) %s\n", $$1, $$2}'

# =============================================================================
# setup — run this once on a fresh server after pulling the repo
# =============================================================================
setup: ## First-time setup: configure .env, build images, start the app
	@
	echo ""
	printf '$(BOLD)$(CYAN)╔══════════════════════════════════════╗\n$(RESET)'
	printf '$(BOLD)$(CYAN)║     ROI Invoicing — Server Setup     ║\n$(RESET)'
	printf '$(BOLD)$(CYAN)╚══════════════════════════════════════╝\n$(RESET)'
	echo ""

	# ── 1. Prerequisites ──────────────────────────────────────────────────────
	printf '$(CYAN)▶ Checking prerequisites…$(RESET)\n'

	if ! command -v docker &>/dev/null; then
	  printf '$(RED)✗ Docker not found. Install Docker Engine 24+ and retry.$(RESET)\n'
	  exit 1
	fi
	printf '  Docker   %s\n' "$$(docker version --format '{{.Server.Version}}' 2>/dev/null || echo '?')"

	if ! docker compose version &>/dev/null; then
	  printf '$(RED)✗ "docker compose" v2 plugin not found.$(RESET)\n'
	  exit 1
	fi
	printf '  Compose  %s\n' "$$(docker compose version --short 2>/dev/null || echo '?')"
	echo ""

	# ── 2. .env ───────────────────────────────────────────────────────────────
	printf '$(CYAN)▶ Configuring environment…$(RESET)\n'
	echo ""

	if [ ! -f .env ]; then
	  cp .env.example .env
	  printf '  Created .env from .env.example\n'
	else
	  printf '  .env already exists — preserving existing values\n'
	fi

	# APP_KEY — generate if absent
	if [ -z "$$(grep -E '^APP_KEY=' .env | cut -d= -f2-)" ]; then
	  new_key="base64:$$(openssl rand -base64 32)"
	  sed -i "s|^APP_KEY=.*|APP_KEY=$$new_key|" .env
	  printf '  $(GREEN)✔$(RESET) APP_KEY  generated\n'
	else
	  printf '  $(GREEN)✔$(RESET) APP_KEY  already set\n'
	fi

	# APP_URL — prompt if still default
	current_url="$$(grep -E '^APP_URL=' .env | cut -d= -f2-)"
	if [ -z "$$current_url" ] || [ "$$current_url" = "http://localhost" ]; then
	  read -rp "  App URL [http://localhost]: " input_url
	  app_url="$${input_url:-http://localhost}"
	  sed -i "s|^APP_URL=.*|APP_URL=$$app_url|" .env
	  printf '  $(GREEN)✔$(RESET) APP_URL  = %s\n' "$$app_url"
	else
	  printf '  $(GREEN)✔$(RESET) APP_URL  = %s\n' "$$current_url"
	fi

	# APP_PORT — prompt if absent
	current_port="$$(grep -E '^APP_PORT=' .env | cut -d= -f2-)"
	if [ -z "$$current_port" ]; then
	  read -rp "  HTTP port [80]: " input_port
	  sed -i "s|^APP_PORT=.*|APP_PORT=$${input_port:-80}|" .env
	  printf '  $(GREEN)✔$(RESET) APP_PORT = %s\n' "$${input_port:-80}"
	else
	  printf '  $(GREEN)✔$(RESET) APP_PORT = %s\n' "$$current_port"
	fi

	# DB passwords — auto-generate strong passwords if absent
	current_dbpw="$$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2-)"
	if [ -z "$$current_dbpw" ]; then
	  db_pw="$$(openssl rand -base64 18 | tr -d '/+=')"
	  db_root_pw="$$(openssl rand -base64 18 | tr -d '/+=')"
	  sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$$db_pw|" .env
	  sed -i "s|^DB_ROOT_PASSWORD=.*|DB_ROOT_PASSWORD=$$db_root_pw|" .env
	  printf '  $(GREEN)✔$(RESET) DB passwords auto-generated and saved to .env\n'
	else
	  printf '  $(GREEN)✔$(RESET) DB passwords already set\n'
	fi

	# Mail settings — prompt; allow skipping for later
	echo ""
	printf '  $(CYAN)Mail settings$(RESET) (Gmail SMTP — press Enter to skip and configure .env later)\n'
	current_mail="$$(grep -E '^MAIL_USERNAME=' .env | cut -d= -f2-)"
	if [ -z "$$current_mail" ] || [ "$$current_mail" = "null" ]; then
	  read -rp "  Gmail address:      " mail_user
	  if [ -n "$$mail_user" ]; then
	    read -rsp "  Gmail app password: " mail_pass
	    echo ""
	    read -rp  "  From address [$$mail_user]: " mail_from
	    mail_from="$${mail_from:-$$mail_user}"
	    sed -i "s|^MAIL_USERNAME=.*|MAIL_USERNAME=$$mail_user|" .env
	    sed -i "s|^MAIL_PASSWORD=.*|MAIL_PASSWORD=\"$$mail_pass\"|" .env
	    sed -i "s|^MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=$$mail_from|" .env
	    printf '  $(GREEN)✔$(RESET) Mail configured\n'
	  else
	    printf '  $(YELLOW)⚠$(RESET)  Skipped — update MAIL_* in .env to enable email\n'
	  fi
	else
	  printf '  $(GREEN)✔$(RESET) Mail already configured\n'
	fi

	echo ""
	printf '$(GREEN)✔ Environment ready$(RESET)\n\n'

	# ── 3. Build Docker images ────────────────────────────────────────────────
	printf '$(CYAN)▶ Building Docker images (first build takes a few minutes)…$(RESET)\n\n'
	$(COMPOSE) build --pull
	echo ""

	# ── 4. Start containers ───────────────────────────────────────────────────
	printf '$(CYAN)▶ Starting containers…$(RESET)\n'
	$(COMPOSE) up -d
	echo ""

	# ── 5. Wait for app to be ready (polls the built-in /up health endpoint) ──
	port="$$(grep -E '^APP_PORT=' .env | cut -d= -f2-)"
	port="$${port:-80}"
	printf '$(CYAN)▶ Waiting for app to be ready on port %s$(RESET)' "$$port"

	attempts=0
	until curl -sf "http://127.0.0.1:$$port/up" &>/dev/null; do
	  printf '.'
	  sleep 3
	  attempts=$$((attempts + 1))
	  if [ "$$attempts" -ge 30 ]; then
	    echo ""
	    printf '$(YELLOW)⚠  Timed out — check logs with: make logs$(RESET)\n'
	    break
	  fi
	done

	if [ "$$attempts" -lt 30 ]; then
	  echo ""
	  printf '$(GREEN)✔ App is up$(RESET)\n'
	fi

	# ── 6. Summary ────────────────────────────────────────────────────────────
	app_url="$$(grep -E '^APP_URL=' .env | cut -d= -f2-)"
	echo ""
	printf '$(BOLD)$(GREEN)╔════════════════════════════════════════╗$(RESET)\n'
	printf '$(BOLD)$(GREEN)║   ✓  Setup complete                    ║$(RESET)\n'
	printf '$(BOLD)$(GREEN)╚════════════════════════════════════════╝$(RESET)\n'
	echo ""
	printf '  %-8s $(BOLD)%s$(RESET)\n'        "URL:"   "$$app_url"
	printf '  %-8s $(BOLD)%s/admin$(RESET)\n'  "Admin:" "$$app_url"
	echo ""
	printf '  $(CYAN)make logs$(RESET)     — follow live logs\n'
	printf '  $(CYAN)make status$(RESET)   — container status\n'
	printf '  $(CYAN)make shell$(RESET)    — shell into the app container\n'
	echo ""

# =============================================================================
# Day-to-day operations
# =============================================================================
up: ## Start all containers
	$(COMPOSE) up -d

down: ## Stop containers (data volumes are preserved)
	$(COMPOSE) down

restart: ## Restart all containers
	$(COMPOSE) restart

build: ## Rebuild images from scratch (no cache)
	$(COMPOSE) build --no-cache --pull

logs: ## Follow live logs (Ctrl-C to exit)
	$(COMPOSE) logs -f

status: ## Show container status
	$(COMPOSE) ps

shell: ## Open a shell inside the app container
	$(COMPOSE) exec app sh

artisan: ## Run an Artisan command  (make artisan cmd="route:list")
	$(COMPOSE) exec app php artisan $(cmd)

migrate: ## Run pending database migrations
	$(COMPOSE) exec app php artisan migrate --force

# =============================================================================
# destroy — destructive, asks for confirmation
# =============================================================================
destroy: ## ⚠  Stop containers AND delete all data volumes (irreversible)
	@
	printf '$(RED)$(BOLD)WARNING: All data including the database will be permanently deleted.$(RESET)\n'
	read -rp "Type 'yes' to confirm: " confirm
	if [ "$$confirm" = "yes" ]; then
	  $(COMPOSE) down -v
	  printf '$(GREEN)Done.$(RESET)\n'
	else
	  printf 'Aborted.\n'
	fi
