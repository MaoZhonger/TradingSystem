# Step 1: Use the official PHP image with Apache
FROM php:8.1-apache

# Step 2: Copy the application code into the container
# Assuming your PHP files are located in the 'AlgoTradingSystem' folder
COPY AlgoTradingSystem/ /var/www/html/

# Step 3: Set the working directory to the application folder
WORKDIR /var/www/html/

# Step 4: Expose the port the container will listen on
EXPOSE 80

# Step 5: Start Apache in the foreground
CMD ["apache2-foreground"]
