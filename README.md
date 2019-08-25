### Translaxor
Translate PO files to desired language via Google Translate API<br/>
This project is built on top of Slim 3 skeleton

#### Install

Run ```composer install``` to install dependencies

#### Usage

Replace ```http://localhost/translaxor``` with your address (local or remote),
Change ```{username}``` with your username.

```bash
curl -X POST -l \
'http://localhost/translaxor' \
-H 'Connection: keep-alive' \
-H 'User-Agent: AgentSmith/7.15.2' \
-H 'Destination-Languages: [EN-RU]' \
-F file=@/Users/{username}/Desktop/source.po \
--output /Users/{username}/Desktop/translations.zip \
&& open /Users/{username}/Desktop/translations.zip \
&& code /Users/{username}/Desktop/translations/.
```