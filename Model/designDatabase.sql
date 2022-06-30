CREATE TABLE account (
	username varchar(20) PRIMARY KEY,
	password varchar(100) NOT NULL,
	email varchar(40) NOT NULL,
	picture_comment_email_notification boolean DEFAULT true
);

CREATE TABLE pictures (
	storagePath varchar(150) PRIMARY KEY,
	creationTime timestamp NOT NULL,
	account_id varchar(20) REFERENCES account(username)
);

CREATE TABLE likes (
	liker_id varchar(20) REFERENCES account(username),
	picture_id varchar(50) REFERENCES pictures(storagePath),
	PRIMARY KEY (liker_id, picture_id),
	time timestamp NOT NULL
);

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TABLE comments (
	id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
	commenter_id varchar(20) REFERENCES account(username),
	picture_id varchar(50) REFERENCES pictures(storagePath),
	content text NOT NULL,
	time timestamp NOT NULL
);
