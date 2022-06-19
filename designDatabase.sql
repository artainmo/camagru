CREATE TABLE account (
	username varchar(20) PRIMARY KEY,
	password varchar(100) NOT NULL,
	email varchar(40) NOT NULL,
	picture_comment_email_notification boolean DEFAULT false
);

CREATE TABLE pictures (
	storagePath varchar(50) PRIMARY KEY,
	creationTime timestamp NOT NULL,
	account_id varchar(20) REFERENCES account(username)
);

CREATE TABLE likes (
	liker_id varchar(20) REFERENCES account(username),
	picture_id varchar(50) REFERENCES pictures(storagePath),
	PRIMARY KEY (liker_id, picture_id),
	time timestamp NOT NULL
);

CREATE TABLE comments (
	id UUID PRIMARY KEY,
	commenter_id varchar(20) REFERENCES account(username),
	picture_id varchar(50) REFERENCES pictures(storagePath),
	content text NOT NULL,
	time timestamp NOT NULL
);
