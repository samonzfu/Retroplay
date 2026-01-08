CREATE USER 
'retroplay'@'localhost' 
IDENTIFIED  BY 'retroplay123';

GRANT USAGE ON *.* TO 'retroplay'@'localhost';


ALTER USER 'retroplay'@'localhost' 
REQUIRE NONE 
WITH MAX_QUERIES_PER_HOUR 0 
MAX_CONNECTIONS_PER_HOUR 0 
MAX_UPDATES_PER_HOUR 0 
MAX_USER_CONNECTIONS 0;

-- dale acceso a la base de datos retroplay
GRANT ALL PRIVILEGES ON retroplay.* 
TO 'retroplay'@'localhost';

-- recarga la tabla de privilegios
FLUSH PRIVILEGES;