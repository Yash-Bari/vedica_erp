<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

class CreateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the database if it does not exist';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $host = env('DB_HOST', '127.0.0.1');
        $database = env('DB_DATABASE', 'vedica_erp_testv1');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');

        try {
            // Attempt to connect to MySQL without selecting a database
            $dsn = "mysql:host=$host";
            $pdo = new PDO($dsn, $username, $password);
            
            // Set the PDO error mode to exception
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if not exists
            $sql = "CREATE DATABASE IF NOT EXISTS `$database`";
            $pdo->exec($sql);
            
            $this->info("Database created successfully or already exists");
            return 0;
        } catch (PDOException $e) {
            $this->error("Error creating database: " . $e->getMessage());
            return 1;
        }
    }
}
