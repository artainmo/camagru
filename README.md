# camagru
42 school [subject](https://cdn.intra.42.fr/pdf/pdf/72690/en.subject.pdf).

In this project we have to write a primitive instagram.

### Stack
Server-side - PHP, PostgreSQL<br>
Client-side - HTML, CSS, Javascript<br>
structure - MVC (unclean see why in Controller/css/style.css)

### Launch

Write the .env file in same directory as docker-compose.yml file with this content:
<pre>
POSTGRES_HOST=postgres
POSTGRES_DB=postgres
POSTGRES_USER=postgres
POSTGRES_PASSWORD=postgres
EMAIL_PASSWORD= #My nickname all lowercase + 123$
CRYPTING_PRIVATE_KEY=AA74CDCC2BBRT935136HH7B63C27
CRYPTING_SECRET_KEY=5fgf5HJ5g27
</pre>
Usually the content of the .env file should not be given as it breaks the purpose of it. But in this case whereby camagru is an exercise I do not mind giving the codes besides the email password as it already got used by spammers when leaving it public.

From root write following command:
<pre>
docker-compose up
</pre>

If wanting to update container with new code before launching:
<pre>
docker-compose build && docker-compose up
</pre>

Once launched go in browser to localhost:8000.

### Notes for evaluations
Visualize the database and encrypted passwords from docker-compose: <br>
Access container terminal from docker app -> type 'psql -U postgres' to access the database with psql -> Do the following SQL command to visualize the account rows 'SELECT * FROM account;'

Pictures without overlay-image pdf contradiction:<br>
correctif -> "You must be able to set none or multiple overlayImages"<br>
subject -> "the button allowing to take the picture should be inactive (not clickable) as long as no superposable image has been selected"

Explain composer use: See Controller/utils/sendmail.php

### Bugs

If error occurs when sending emails, it probably means the email address camagru19@hotmail.com got locked, to resolve the problem go unlock it by connecting on outlook to that email account. After you unlocked the account, you may need to wait 5min before you can actually send emails again.
