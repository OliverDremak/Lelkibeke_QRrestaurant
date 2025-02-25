FROM node:18.14.2

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

EXPOSE 3000

# Build the application
RUN npm run build

# Change the start command to use the correct Nuxt command
CMD ["npm", "run", "start"]

