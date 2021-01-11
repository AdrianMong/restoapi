create database cantine_rest_api;
use cantine_rest_api;

create table etudiant
(
    etu varchar(15)primary key not null,
    token varchar(50),
    dateExpiration date, 
    nom varchar(20)not null,
    pwd varchar(50)not null,
    dateNaissance date not null
);

CREATE TABLE Categorie(
    idCategorie int primary key auto_increment,
    nomCategorie varchar(50)
);

CREATE TABLE Plat(
    codePlat int primary key auto_increment,
    intitule varchar(25),
    prix double not null,
    idCategorie int not null,
    foreign key (idCategorie) references Categorie(idCategorie)
);

CREATE TABLE Menu(
    idMenu int primary key auto_increment,
    jour date
);

CREATE TABLE PlatsMenu(
    codePlat int,
    idMenu int,
    foreign key (codePlat) references Plat(codePlat),
    foreign key(idMenu) references Menu(idMenu)
);
create unique index doublon_plat_menu on PlatsMenu(codePlat,idMenu);

create table commandeEtudiant
(
    idCommandeEtudiant int primary key auto_increment,
    etu varchar(15)not null,
    dateJour date not null
);
create unique index commande_unique_journalier on commandeEtudiant(etu,dateJour);

create table commandePlatEtudiant
(
    idCommandeEtudiant int not null,
    codePlat int not null,
    quantite int not null,
    actif varchar(1)not null default 'Y',
    foreign key(idCommandeEtudiant) REFERENCES commandeEtudiant(idCommandeEtudiant),
    foreign key(codeplat) REFERENCES plat(codePlat)
);
create unique index commande_plat_unique on commandePlatEtudiant(idCommandeEtudiant,codePlat);

insert into etudiant(etu,nom,pwd,dateNaissance) values ('ETU0001','Etudiant 1',sha1('pwd1'),'2001-5-10'),
                            ('ETU0002','Etudiant 2',sha1('pwd2'),'2000-3-21'),
                            ('ETU0003','Etudiant 3',sha1('pwd3'),'2002-10-6'),
                            ('ETU0004','Etudiant 4',sha1('pwd4'),'2000-1-25'),
                            ('ETU0005','Etudiant 5',sha1('pwd5'),'2004-12-5'),
                            ('ETU0006','Etudiant 6',sha1('pwd6'),'2003-1-2'),
                            ('ETU0007','Etudiant 7',sha1('pwd7'),'2002-10-23'),
                            ('ETU0008','Etudiant 8',sha1('pwd8'),'2000-3-12'),
                            ('ETU0009','Etudiant 9',sha1('pwd9'),'2000-11-14'),
                            ('ETU0010','Etudiant 10',sha1('pwd10'),'2000-6-20');

insert into Categorie(nomCategorie) values  ('Entree'),
                                            ('Plat'),
                                            ('Dessert');

insert into Plat(intitule,prix,idCategorie) values  ('Plat 1',1000,1),
                                                    ('Plat 2',2000,2),
                                                    ('Plat 3',3000,3),
                                                    ('Plat 4',4000,1),
                                                    ('Plat 5',5000,2),
                                                    ('Plat 6',6000,3),
                                                    ('Plat 7',7000,1),
                                                    ('Plat 8',8000,2),
                                                    ('Plat 9',9000,3),
                                                    ('Plat 10',1500,1),
                                                    ('Plat 11',2500,2),
                                                    ('Plat 12',3500,3),
                                                    ('Plat 13',4500,1),
                                                    ('Plat 14',6500,3),
                                                    ('Plat 15',5500,2);

insert into Menu(jour) values(CURRENT_DATE);

insert into PlatsMenu values (1,1),
                            (4,1),
                            (11,1),
                            (15,1),
                            (9,1),
                            (12,1);




-- Plat du jour
create view platdujour as
    select Plat.*,Categorie.nomCategorie from Plat join Categorie on Plat.idCategorie=Categorie.idCategorie where codeplat in (select codeplat from PlatsMenu join Menu on PlatsMenu.idMenu=Menu.idMenu where jour=current_date);
-- Liste plat a preparer
create view platapreparer as
    select Plat.intitule,count(*) quantite from commandePlatEtudiant join Plat on Plat.codePlat=commandePlatEtudiant.codePlat where commandePlatEtudiant.idCommandeEtudiant in (select idCommandeEtudiant from commandeEtudiant where dateJour=current_date) group by commandePlatEtudiant.codeplat;
--Trigger verification menu avant insertion commande

select sum(quantite*prix) montant from plat join (select codeplat,quantite from commandePlatEtudiant where actif='Y' and idCommandeEtudiant=(select idCommandeEtudiant from commandeEtudiant where etu='ETU945' and dateJour=current_date)) q1 on plat.codePlat=q1.codePlat;
select plat.intitule,sum(quantite) quantite from commandePlatEtudiant join plat on commandePlatEtudiant.codePlat=plat.codePlat where idCommandeEtudiant in(select idCommandeEtudiant from commandeEtudiant where dateJour=current_date) and actif='Y' group by commandePlatEtudiant.codeplat;
select plat.intitule,commandePlatEtudiant.* from commandeplatetudiant join plat on commandePlatEtudiant.codePlat=plat.codePlat where idCommandeEtudiant in(select idcommandeEtudiant from commandeEtudiant where etu='ETU945' and datejour=current_date)