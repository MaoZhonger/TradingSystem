# Step 1: Use the official PHP image with Apache
FROM php:8.1-apache

# Step 2: Copy the application code into the container
# Assuming your PHP files are located in the 'AlgoTradingSystem' folder
COPY AlgoTradingSystem/ /var/www/html/

# Step 3: Set the working directory to the application folder
WORKDIR /var/www/html/

# Step 4: Set correct permissions for the application directory
# Set ownership to www-data user and group (Apache's default user)
RUN chown -R www-data:www-data /var/www/html/

# Set permissions for the entire directory (recursively) to allow read and write access for all users
RUN chmod -R 777 /var/www/html/

# Step 5: Expose the port the container will listen on
EXPOSE 80

# Step 6: Start Apache in the foreground
CMD ["apache2-foreground"]
