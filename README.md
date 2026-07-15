# EasyFlex2GoPlus

Laravel 11 project running locally with Laravel Sail on WSL2.

## Prerequisites (WSL2)

1. Docker Desktop installed on Windows.
2. Docker Desktop WSL integration enabled for your distro.
3. Project stored inside Linux filesystem (recommended path: `/home/...`, not `/mnt/c/...`).
4. PHP and Composer installed in WSL.

## First-Time Setup

Run these commands from the project root:

```bash
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
```

Open: http://localhost

Use token access in URL, for example: `http://localhost/?token=token123`

## Daily Commands

```bash
./vendor/bin/sail up -d
./vendor/bin/sail down
```

## If Docker Fails in WSL2

If you get `/usr/bin/docker: Input/output error`:

1. Restart Docker Desktop.
2. In Windows PowerShell, run:

```powershell
wsl --shutdown
```

3. Re-open WSL and verify:

```bash
docker --version
docker compose version
```

4. Start Sail again:

```bash
./vendor/bin/sail up -d
```

## Deploy to Azure Container Apps (GitHub Actions)

A workflow is included at `.github/workflows/deploy-azure-container-app.yml`.

It will:

1. Build your image in Azure Container Registry (ACR).
2. Deploy that image to Azure Container Apps.
3. Create the container app automatically if it does not exist yet.

### 1) Prepare Azure resources (one-time)

Create these resources in the same subscription:

- Resource Group
- Azure Container Registry (ACR)
- Azure Container Apps Environment
- (Optional) Container App ahead of time

If you prefer command line, this is a reference flow:

```bash
RG=rg-easyflex2goplus
LOC=westeurope
ACR=acreasyflex2goplus
ENV=acae-easyflex2goplus

az group create -n "$RG" -l "$LOC"
az acr create -n "$ACR" -g "$RG" -l "$LOC" --sku Basic
az provider register --namespace Microsoft.App
az provider register --namespace Microsoft.OperationalInsights
az containerapp env create -n "$ENV" -g "$RG" -l "$LOC"
```

### 2) Configure GitHub variables

In GitHub repo settings -> Secrets and variables -> Actions -> Variables, add:

- `AZURE_RESOURCE_GROUP`
- `AZURE_CONTAINER_APP_NAME`
- `AZURE_CONTAINER_APPS_ENVIRONMENT`
- `AZURE_ACR_NAME`
- `AZURE_ACR_REPOSITORY` (example: `easyflex2goplus-app`)

### 3) Configure GitHub secrets

Add these secrets:

- `AZURE_CLIENT_ID`
- `AZURE_TENANT_ID`
- `AZURE_SUBSCRIPTION_ID`
- `APP_ENV_PROD` (full content of production `.env` file)

For first automatic container app creation only, also add:

- `ACR_USERNAME`
- `ACR_PASSWORD`

You can get ACR credentials with:

```bash
az acr credential show -n <your-acr-name>
```

### 4) Configure Azure login from GitHub (OIDC)

Create an Entra ID app/service principal with federated credentials for your GitHub repo and grant it permissions on the resource group (Contributor is simplest to start).

Reference docs:

- https://learn.microsoft.com/azure/developer/github/connect-from-azure-openid-connect

### 5) Deploy

Push to `main` or run the workflow manually from GitHub Actions.

After successful run, the workflow summary prints the deployed image and app URL.
