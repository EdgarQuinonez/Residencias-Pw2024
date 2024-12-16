<?php
    $db = new SQLite3(__DIR__ . "/sqlite.db");
    $env = parse_ini_file(__DIR__ . "/.env");
    
    class User {        
        
        protected static function __create_table() {

            global $db;
            
            $res = $db->exec('CREATE TABLE IF NOT EXISTS Usuario (                                
                "Username" TEXT,
                "Password" TEXT,
                "Role" TEXT NOT NULL DEFAULT User,
                "CreatedAt" DATETIME DEFAULT CURRENT_TIMESTAMP                
            )');

            if (!$res) {
                $errMsg = $db->lastErrorMsg();
                throw new Exception("Error al crear la tabla usuario: $errMsg");
            }                               
        }
        public static function register_user($username, $password) {
            User::__create_table();
            global $db;
            try {
                if (empty($username) || empty($password)) {
                    throw new Exception("El usuario y la contraseña deben de estar definidos.");
                }

                if (User::__user_exists($username)) {                    
                    throw new Exception("Este usuario ya existe. Prueba uno nuevo.");
                }

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);                

                $sql = 'INSERT INTO Usuario (Username, Password) VALUES (:username, :password)';
                $sth = $db->prepare($sql);
                $sth->bindValue("username", $username, SQLITE3_TEXT);
                $sth->bindValue("password", $hashedPassword, SQLITE3_TEXT);
                // $sth->bindValue("password", $password);

                $result = $sth->execute();

                if (!$result) {
                    throw new Exception("Hubo un error al procesar la solicitud de registro de usuario.");
                }

                return ['message' => "Usuario creado con éxito", 'ok' => true];
            } catch (Exception $e) {
                return ["message" => $e->getMessage(), "ok" => false];
            }
        }

        public static function login_user_with_password($username, $password) {
            try {
                User::__create_table();
                global $db;                
                if (empty($username) || empty($password)) {
                    throw new Exception("El usuario y la contraseña deben de estar definidos.");
                }
                
                $sql = 'SELECT ROWID, * FROM Usuario WHERE Username = :username';
                $sth = $db->prepare($sql);
                $sth->bindValue('username', $username, SQLITE3_TEXT);

                $results = $sth->execute();                
                               
                if (!$results) {
                    $errMsg = $db->lastErrorMsg();                    
                    throw new Exception("No se completó la query: $errMsg");                
                }                                                            

                $results->reset();
                for ($nrows = 0; is_array($results->fetchArray()); $nrows++);
                $results->reset();
                
                if ($nrows === 0) {
                    throw new Exception("El usuario no está registrado.");
                }
                $data = [];
                for ($i = 0; $i < $nrows; $i++) {
                    array_push($data, $results->fetchArray());
                }

                $hashedPassword = $data[0]["Password"];
                if (!password_verify($password, $hashedPassword)) {
                    throw new Exception("La contraseña proporcionada es incorrecta.");
                }
                // return ["message" => print_r($data, true), "ok" => false];
                Token::set_auth_user_session($data[0]["Username"]);
                
                return ["message" => "Inicio de sesión exitoso", 'ok' => true];                
            } catch (Exception $e) {
                return ["message" => $e->getMessage(), "ok" => false];
            }
        }

        protected static function __user_exists($username) {

            global $db;

            $sql = 'SELECT * FROM Usuario WHERE Username = :username';
            $sth = $db->prepare($sql);
            $sth->bindValue('username', $username, SQLITE3_TEXT);            

            $results = $sth->execute();
            if (!$results) {
                throw new Exception("No se pudo completar la solicitud de buscar al usuario.");                
            }

            for ($nrows = 0; is_array($results->fetchArray()); $nrows++);            
                      
            return $nrows != 0;            
        }

        public static function logout_user() {
            try {
                Token::remove_user_session();
                return ['message'=>"Usuario cerró sesión con éxito", "ok" => true];
            } catch (Exception $e) {
                return ['message'=>$e->getMessage(), 'ok'=>false];
            }
        }
    }

    class Superuser extends User {
        public static function register_superuser($username, $password) {
            Superuser::__create_table();
            global $db;
            try {
                if (empty($username) || empty($password)) {
                    throw new Exception("El usuario y la contraseña deben de estar definidos.");
                }

                if (Superuser::__user_exists($username)) {                    
                    throw new Exception("Este usuario ya existe. Prueba uno nuevo.");
                }

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);                

                $sql = 'INSERT INTO Usuario (Username, Password, Role) VALUES (:username, :password, :role)';
                $sth = $db->prepare($sql);
                $sth->bindValue("username", $username, SQLITE3_TEXT);
                $sth->bindValue("password", $hashedPassword, SQLITE3_TEXT);
                $sth->bindValue("role", 'Admin', SQLITE3_TEXT);
                // $sth->bindValue("password", $password);

                $result = $sth->execute();

                if (!$result) {
                    throw new Exception("Hubo un error al procesar la solicitud de registro de superusuario.");
                }

                return ['message' => "Superusuario creado con éxito", 'ok' => true];
            } catch (Exception $e) {
                return ["message" => $e->getMessage(), "ok" => false];
            }
        }
    }
    
    class Token {
        private static function __create_table() {
            global $db;
            $sql = 'CREATE TABLE IF NOT EXISTS UserToken (
                uid INTEGER UNIQUE NOT NULL,
                Token TEXT
            )';

            $res = $db->exec($sql);

            if (!$res) {
                $errMsg = $db->lastErrorMsg();
                throw new Exception("Error al crear la tabla UserToken: $errMsg");
            }    
        }

        private static function __generate_auth_token() {
            $hash = openssl_random_pseudo_bytes(256);
            return $hash;
        }

        public static function set_auth_user_session($uid)  {
            global $db;
            global $env;
            
            Token::__create_table();
            // INSERT into USERTOKEN and expose it to client as cookie so I can later check if user is in the table.
            $sql = 'INSERT INTO UserToken (
                uid,
                Token
            ) VALUES (
                :uid,
                :token
            )';

            $token = Token::__generate_auth_token();

            $sth = $db->prepare($sql);
            $sth->bindValue("uid", $uid, SQLITE3_TEXT);
            $sth->bindValue("token", $token, SQLITE3_TEXT);

            $results = $sth->execute();

            if (!$results) {
                $errorMsg = $db->lastErrorMsg();
                throw new Exception("Se presentó un error al establecer la sesión del usuario: $errorMsg");
            }

            $cookie = "$uid:$token";
            $mac = hash_hmac("sha256", $cookie, $env["SECRET_KEY"]);
            $cookie .= ":$mac";
            
            setcookie("rememberme", $cookie); 
        }

        public static function remove_user_session() {
            $cookie = isset($_COOKIE['rememberme']) ? $_COOKIE['rememberme'] : '';

            if (!$cookie) {
                throw new Exception("Error al remover la sesión del usuario. No existe una cookie rememberme.");
            }

            global $db;
            [ $user, $token, $mac ] = explode(':', $cookie);
            
            $sql = 'DELETE FROM UserToken WHERE uid = :uid';
            $sth = $db->prepare($sql);
            $sth->bindValue("uid", $user);

            $results = $sth->execute();

            if (!$results) {
                $errorMsg = $db->lastErrorMsg();
                throw new Exception("Ocurrio un error al cerrar la sesión del usuario: $errorMsg");
            }            
        }

        public static function remember_me() {
            try {

                $cookie = isset($_COOKIE['rememberme']) ? $_COOKIE['rememberme'] : '';

                if (!$cookie) {
                    throw new Exception('La cookie está vacía.');
                }
                
                global $env;
                [ $user, $token, $mac ] = explode(':', $cookie);
                if (!hash_equals(hash_hmac('sha256', "$user:$token",  $env['SECRET_KEY']), $mac)) {
                    throw new Exception('La cookie es inválida.');
                }
                $usertoken = Token::__fetch_token_by_username($user);
                if (hash_equals($usertoken, $token)) {
                    // User is logged in or should be
                    return ['message'=>'Inicio de sesión exitoso. Cookie válida', 'cookieIsValid'=>true ];
                    
                }
                
            } catch (Exception $e) {
                return ['message'=>$e->getMessage(), 'cookieIsValid'=>false ];
            }
        }

        private static function __fetch_token_by_username($username) {
            global $db;
            $sql = 'SELECT * FROM UserToken WHERE uid = :username';
            $sth = $db->prepare($sql);
            $sth->bindValue('username', $username);

            $results = $sth->execute();

            if (!$results) {
                $errorMsg = $db->lastErrorMsg();
                throw new Exception("Ocurrió un error al fetch token: $errorMsg");
            }

            $results->reset();
            for ($nrows = 0; is_array($results->fetchArray()); $nrows++);
            $results->reset();
            
            if ($nrows === 0) {
                throw new Exception("El usuario no tiene una sesión activa");
            }
            $data = [];
            for ($i = 0; $i < $nrows; $i++) {
                array_push($data, $results->fetchArray());
            }
            
            return $data[0]['Token'];                                   
        }


    }

    class Reporte {
        protected static function __create_table() {
            global $db;
            
            $res = $db->exec('CREATE TABLE IF NOT EXISTS Reporte (
                "Id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                "Title" TEXT NOT NULL,                
                "FechaPublicacion" DATETIME,
                "AsesorInterno" TEXT,
                "AsesorExterno" TEXT,
                "URI" TEXT,
                "CreatedAt" DATETIME DEFAULT CURRENT_TIMESTAMP                
            )');

            if (!$res) {
                $errMsg = $db->lastErrorMsg();
                throw new Exception("Error al crear la tabla Reporte: $errMsg");
            }                               
        }

        public static function get_all($limit = 20, $offset = 0) {
            try {
                
                global $db;

                Reporte::__create_table();
                Autor::create_table();
                AutorReporte::create_table();
    
                $sql = '
                    SELECT 
                        r.Title,
                        r.FechaPublicacion,
                        a.Nombre AS NombreAutor,
                        a.NoControl AS NoControlAutor,
                        r.AsesorInterno,
                        r.AsesorExterno
                    FROM Reporte r
                    INNER JOIN AutorReporte ar ON ar.ReporteID = r.Id
                    INNER JOIN Autor a ON a.NoControl = ar.NoControl
                    ORDER BY r.FechaPublicacion
                    LIMIT :limit
                    OFFSET :offset
                ';

                $sth = $db->prepare($sql);
                $sth->bindValue('limit', $limit);
                $sth->bindValue('offset', $offset);
                        
                $results = $sth->execute();
                if (!$results) {
                    throw new Exception("No se completó la solicitud de buscar los reportes.");                
                }
    
                $results->reset();
                for ($nrows = 0; is_array($results->fetchArray()); $nrows++);
                $results->reset();            
                          
                if ($nrows === 0) {
                    throw new Exception("No hay registros de reportes.");
                }

                $data = [];
                for ($i = 0; $i < $nrows; $i++) {
                    array_push($data, $results->fetchArray());
                }

                return ["message"=>"Reportes recuperados con éxito.", 'data'=>$data];

            } catch (Exception $e) {
                return ["message"=>$e->getMessage(), 'data'=>null];
            }

        }

        public static function upload_file($title, $authors,  $publishDate = null, $asesorInterno = null, $asesorExterno = null, $uri = null) {
            try {

                if ($title === null || empty($title)) {
                    throw new Exception("El título no puede ser nulo.");
                }

                if (!isset($authors)) {
                    throw new Exception("El reporte debe de tener al menos un autor.");
                }

                $authorsArr = json_decode($authors, true);

                global $db;

                Reporte::__create_table();
                Autor::create_table();
                AutorReporte::create_table();
    
                $sql = '
                    INSERT INTO Reporte (Title, FechaPublicacion, AsesorInterno, AsesorExterno, URI) 
                    VALUES (:title, :publishDate, :asesorInterno, :asesorExterno, :uri)
                ';

                $sth = $db->prepare($sql);
                
                $sth->bindValue('title', $title);
                $sth->bindValue('publishDate', $publishDate);
                $sth->bindValue('asesorInterno', $asesorInterno);
                $sth->bindValue('asesorExterno', $asesorExterno);
                $sth->bindValue('uri', $uri);
                        
                $results = $sth->execute();
                if (!$results) {
                    throw new Exception("No se completó la solicitud de subir el archivo.");                
                }
                            
                $reportID = $db->lastInsertRowID();

                foreach ($authorsArr as $a) {
                    ['noControl'=>$noControl, 'name'=>$name] = $a;
                    Autor::create_author($noControl, $name);
                    AutorReporte::create_record($noControl, $reportID);
                }
             

                return ['message'=>"Archivo cargado exitosamente.", 'ok'=>true];
            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
                return ['message'=>"Error al subir el archivo: $errorMsg", 'ok'=>false];
            }

        }

        

        public static function delete_file()  {
            
        }        

        public static function filter_by_column($column_name, $filter_value) {
            global $db;

            $valid_columns = [
                "Title",
                "FechaPublicacion",
                "AutorNoControl",
                "AutorNombre",
                "AsesorInterno",
                "AsesorExterno",
                "CreatedAt"
            ];

            if (!in_array($column_name, $valid_columns)) {
                throw new Exception("La columna seleccionada no es un parámetro válido.");
            }


            $sql = 'SELECT * FROM Reporte WHERE :column = :value';
            $sth = $db->prepare($sql);
            $sth->bindValue('column', $column_name, SQLITE3_TEXT);            
            $sth->bindValue('value', $filter_value, SQLITE3_TEXT);            

            $results = $sth->execute();
            if (!$results) {
                throw new Exception("No se pudo completar la solicitud de buscar al usuario.");                
            }

            for ($nrows = 0; is_array($results->fetchArray()); $nrows++);            
                      
            return $nrows != 0;           
        }
        
    }

    class Autor {
        public static function create_table() {
            global $db;            
            $res = $db->exec('CREATE TABLE IF NOT EXISTS Autor (                                
                "NoControl" TEXT PRIMARY KEY,              
                "Nombre" TEXT,
                "CreatedAt" DATETIME DEFAULT CURRENT_TIMESTAMP                
            )');

            if (!$res) {
                $errMsg = $db->lastErrorMsg();
                throw new Exception("Error al crear la tabla Autor: $errMsg");
            }                               
        }

        public static function create_author($noControl, $name) {
            
            if ($noControl === null || $name === null) {
                throw new Exception("Error al crear autor: Número de control y nombre deben de estar definidos.");
            }
            
            global $db;                
            Autor::create_table();
            AutorReporte::create_table();
            $sql = '
                INSERT INTO Autor (NoControl, Nombre) VALUES (:noControl, :name)
            ';

            $sth = $db->prepare($sql);
            $sth->bindValue('noControl', $noControl);
            $sth->bindValue('name', $name);
                    
            $results = $sth->execute();
            if (!$results) {
                throw new Exception("No se completó la solicitud de crear un autor.");                
            }                    
        }
    }

    class AutorReporte {
        public static function create_table() {
            global $db;            
            $res = $db->exec('CREATE TABLE IF NOT EXISTS AutorReporte (                                
                NoControl TEXT,              
                ReporteID INTEGER,
                FOREIGN KEY (NoControl) REFERENCES Autor(NoControl),
                FOREIGN KEY (ReporteID) REFERENCES Reporte(Id)
            )');

            if (!$res) {
                $errMsg = $db->lastErrorMsg();
                throw new Exception("Error al crear la tabla AutorReporte: $errMsg");
            }                               
        }

        public static function create_record($authorNoControl, $reporteID) {
            if ($authorNoControl === null || $reporteID === null) {
                throw new Exception("Error al crear relación autor-reporte: Número de control y reporteID deben estar definidos.");
            }
            
            global $db;                
            Autor::create_table();
            AutorReporte::create_table();
            $sql = '
                INSERT INTO AutorReporte (NoControl, ReporteID) VALUES (:noControl, :reporteID)
            ';

            $sth = $db->prepare($sql);
            $sth->bindValue('noControl', $authorNoControl);
            $sth->bindValue('reporteID', $reporteID);
                    
            $results = $sth->execute();
            if (!$results) {
                throw new Exception("No se completó la solicitud de crear un autor-reporte.");                
            } 

        }
    }
