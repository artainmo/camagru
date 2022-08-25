# camagru
https://cdn.intra.42.fr/pdf/pdf/52596/en.subject.pdf

### STACK
Server-side - PHP<br>
Client-side - HTML, CSS, Javascript<br>
structure - MVC (unclean see why in Controller/css/style.css)

### LAUNCH

Write the .env file in same directory as docker-compose.yml file with this content:
<pre>
POSTGRES_HOST=postgres
POSTGRES_DB=postgres
POSTGRES_USER=postgres
POSTGRES_PASSWORD=postgres
EMAIL_PASSWORD= #My nickname all lowercase + 123$
CRYPTING_PRIVATE_KEY=AA74CDCC2BBRT935136HH7B63C27
CRYPTING_SECRET_KEY=5fgf5HJ5g27
<pre>
Usually the content of the .env file should not be given as it breaks the purpose of it. But in this case whereby camagru is an exercise I do not mind giving the codes besides the email password as it already got used by spammers when leaving it public.

From root write following command:
<pre>
docker-compose up
</pre>

#### Bugs

If error occurs when sending emails, it probably means the email address camagru19@hotmail.com got locked, to resolve the problem go unlock it by connecting on outlook to that email account.
