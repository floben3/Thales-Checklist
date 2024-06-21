import mysql.connector

def get_db_connection():
    """Establishes a connection to the MySQL database and returns the connection object."""
    conn = mysql.connector.connect(
        host = '',  # MySQL server hostname or IP address
        user = '',  # MySQL username
        password = '',  # MySQL password
        database = ''  # MySQL database name
    )
    return conn