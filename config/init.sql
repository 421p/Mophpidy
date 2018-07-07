create table users
(
  id bigint not null
    primary key
)
;

create table notifications
(
  id integer
    primary key
  autoincrement,
  user_id bigint
    constraint notifications_users_id_fk
    references users
)
;

create unique index notifications_user_id_uindex
  on notifications (user_id)
;