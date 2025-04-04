# Use the official PHP image with Apache installed
FROM php:8.1-apache

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy the entire project folder into the container's web directory
COPY . /var/www/html/

# Ensure the permissions are set properly for files (since your code was developed on XAMPP)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 for the web server
EXPOSE 80

# Start Apache in the foreground when the container is run
CMD ["apache2-foreground"]
