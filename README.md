# Fichier .env à modifier 
Nom d’utilisateur "root" 
Mot de passe "root"
Nom de DB "projet_symfony"
# Installer les dépendances PHP 
composer install 
# Installer les dépendances JavaScript 
npm install ou yarn install 
# Générer les actifs 
npm run dev  ou yarn dev
 # Créer la base de données (si nécessaire) 
php bin/console doctrine:database:create 
# Exécuter les migrations 
php bin/console doctrine:migrations:migrate
