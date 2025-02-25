# Stage 1: Build stage
FROM node:22-slim AS builder

WORKDIR /app

# Másold be a csomagfájlokat
COPY package*.json ./

# Telepítsd a függőségeket
RUN npm install --non-interactive

# Másold be a többi forráskódot
COPY . .

# Futtasd a Nuxt build parancsot, ami létrehozza a .output mappát
RUN npm run build

# Stage 2: Production image
FROM node:22-slim

WORKDIR /app

# Másold át a production függőségeket (ha szükséges)
COPY package*.json ./
RUN npm install --omit=dev --non-interactive

# Másold át a build outputot a builder stage-ből
COPY --from=builder /app/.output ./.output

# Ha a forráskód szükséges runtime konfigurációhoz, akkor másold át (pl. public mappa)
COPY --from=builder /app/public ./public

# Állítsd be a host-ot
ENV HOST 0.0.0.0
ENV PORT 3000

EXPOSE 3000

# Indítsd el a Nuxt alkalmazást
CMD ["npm", "run", "start"]

