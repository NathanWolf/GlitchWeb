create schema glitch;
use glitch;

create user 'glitch'@'127.0.0.1' identified by 'super-secure-password';
grant select ON glitch.* to 'glitch'@'127.0.0.1';
grant update,insert ON glitch.user to 'glitch'@'127.0.0.1';
grant update,insert ON glitch.user_property to 'glitch'@'127.0.0.1';

create user 'glitch_admin'@'127.0.0.1' identified by 'super-duper-secure-password';
grant all privileges ON glitch.* to 'glitch_admin'@'127.0.0.1';

create table user
(
    id VARCHAR(40) DEFAULT (uuid()),
    email varchar(255) not null,
    first_name varchar(255) null,
    last_name varchar(255) null,
    created timestamp not null default CURRENT_TIMESTAMP,
    updated timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    password_hash varchar(255) not null,
    admin bool default false,
    token varchar(255) null,

    constraint user_pk
        primary key (id)
);

CREATE TABLE user_property
(
    user_id VARCHAR(64) NOT NULL,
    property_id VARCHAR(64) NOT NULL,
    value varchar(255) NOT NULL,

    constraint user_property_pk
        primary key (user_id, property_id),

    foreign key (user_id)
        references user(id)
        on delete cascade
        on update cascade
);

create table tier_list
(
    id VARCHAR(255) NOT NULL,
    created timestamp not null default CURRENT_TIMESTAMP,
    updated timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    name varchar(255) NOT NULL,

    constraint tier_list_pk
        primary key (id)
);

create table tier
(
    id VARCHAR(255) NOT NULL,
    tier_list_id VARCHAR(255) NOT NULL,
    created timestamp not null default CURRENT_TIMESTAMP,
    updated timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(64) NOT NULL,
    dark boolean default false,

    constraint tier_pk
        primary key (id, tier_list_id),

    foreign key (tier_list_id)
        references tier_list(id)
        on delete cascade
        on update cascade
);

alter table tier
    add priority int not null default 0;

create table persona
(
    id VARCHAR(64) NOT NULL,
    created timestamp not null default CURRENT_TIMESTAMP,
    updated timestamp not null default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    first_name varchar(255) null,
    last_name varchar(255) null,
    chat JSON null,
    portrait JSON null,
    backstory LONGTEXT null,

    constraint persona_pk
        primary key (id)
);

alter table persona
    add column description LONGTEXT null;

alter table persona
    add column image JSON null;

create table property
(
    id VARCHAR(64) NOT NULL,
    name varchar(255) NOT NULL,

    constraint property_pk
        primary key (id)
);

insert into property (id, name) values ('sexuality', 'Sexuality');
insert into property (id, name) values ('species', 'Species');
insert into property (id, name) values ('pronouns', 'Pronouns');
insert into property (id, name) values ('title', 'Title');

alter table property
    add column question text null,
    add column priority int default 0,
    add column plural boolean default false;

CREATE TABLE persona_property
(
    persona_id VARCHAR(64) NOT NULL,
    property_id VARCHAR(64) NOT NULL,
    value varchar(255) NOT NULL,

    constraint persona_property_pk
        primary key (persona_id, property_id),

    foreign key (persona_id)
        references persona(id)
        on delete cascade
        on update cascade,

    foreign key (property_id)
        references property(id)
        on delete cascade
        on update cascade
);

create table relationship
(
    id VARCHAR(64) NOT NULL,
    name varchar(255) NOT NULL,

    constraint relationship_id
        primary key (id)
);

CREATE TABLE persona_relationship
(
    persona_id VARCHAR(64) NOT NULL,
    relationship_id VARCHAR(64) NOT NULL,
    related_persona_id VARCHAR(64) NOT NULL,

    constraint persona_relationship_pk
        primary key (persona_id, relationship_id, related_persona_id),

    foreign key (persona_id)
        references persona(id)
        on delete cascade
        on update cascade,

    foreign key (relationship_id)
        references relationship(id)
        on delete cascade
        on update cascade,

    foreign key (related_persona_id)
        references persona(id)
        on delete cascade
        on update cascade
);

CREATE TABLE persona_tier
(
    persona_id VARCHAR(64) NOT NULL,
    tier_list_id VARCHAR(64) NOT NULL,
    tier_id varchar(255) NOT NULL,

    constraint persona_property_pk
        primary key (persona_id, tier_list_id),

    foreign key (persona_id)
        references persona(id)
        on delete cascade
        on update cascade,

    foreign key (tier_list_id)
        references tier_list(id)
        on delete cascade
        on update cascade,

    foreign key (tier_list_id, tier_id)
        references tier(tier_list_id, id)
        on delete cascade
        on update cascade
);

alter table persona_tier
    add priority int not null default 0;

create table quiz
(
    id VARCHAR(64) NOT NULL,
    name varchar(255) NOT NULL,

    constraint quiz_pk
        primary key (id)
);

create table quiz_question
(
    id int auto_increment not null,
    quiz_id varchar(64) not null,
    question TEXT NOT NULL,
    explanation TEXT NULL,

    constraint quiz_question_pk
        primary key (id),

    foreign key (quiz_id)
        references quiz(id)
        on delete cascade
        on update cascade
);

create table quiz_answer
(
    id int auto_increment not null,
    quiz_question_id int not null,
    answer TEXT NOT NULL,
    correct boolean not null default false,

    constraint quiz_answer_pk
        primary key (id),

    foreign key (quiz_question_id)
        references quiz_question(id)
        on delete cascade
        on update cascade
);
