# "DBNAME", "PASSWD" 를 Service에 사용될 Database Name 으로 수정할것.
# mysql 'root' user 로 실행 할 것.
# 2019.7.5. hamt.

CREATE database itam;
GRANT ALL privileges on itam.* to itam@localhost identified by 'make#secu' with grant option;
FLUSH PRIVILEGES;
