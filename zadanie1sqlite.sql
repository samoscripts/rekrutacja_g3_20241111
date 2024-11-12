DROP TABLE IF EXISTS zadanie;

CREATE TABLE zadanie (
     id_form INTEGER PRIMARY KEY CONSTRAINT id_form_pk,
     name TEXT NOT NULL,
     surname TEXT NOT NULL,
     email TEXT NOT NULL,
     phone TEXT NOT NULL,
     client_no TEXT NOT NULL,
     choose INTEGER NOT NULL,
     agreement1 INTEGER NOT NULL,
     agreement2 INTEGER NOT NULL,
     agreement3 INTEGER NOT NULL,
     user_info TEXT,
     account TEXT,
     date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
