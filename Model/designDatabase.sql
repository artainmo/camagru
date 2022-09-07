CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

CREATE TABLE IF NOT EXISTS account (
	id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
	username varchar(20) NOT NULL,
	password varchar(100) NOT NULL,
	email varchar(40) NOT NULL,
	picture_comment_email_notification boolean DEFAULT true
);

CREATE TABLE IF NOT EXISTS pictures (
	storagePath varchar(150) PRIMARY KEY,
	creationTime timestamp NOT NULL,
	account_id UUID REFERENCES account(id)
);

CREATE TABLE IF NOT EXISTS likes (
	liker_id UUID REFERENCES account(id),
	picture_id varchar(150) REFERENCES pictures(storagePath),
	PRIMARY KEY (liker_id, picture_id),
	time timestamp NOT NULL
);

CREATE TABLE IF NOT EXISTS comments (
	id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
	commenter_id UUID REFERENCES account(id),
	picture_id varchar(150) REFERENCES pictures(storagePath),
	content text NOT NULL,
	time timestamp NOT NULL
);
