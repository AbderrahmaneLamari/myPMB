/*
 * Copyright (c) 1995-2003, Index Data.
 * See the file LICENSE for details.
 *
 * $Id: ztest.c,v 1.1.44.1 2023/03/02 14:42:10 jparis Exp $
 */

/*
 * Demonstration of simple server
 */
#if (defined(_WIN32) || defined(_WIN64)) && !defined(__WIN__)
#define __WIN__
#endif

#include <stdio.h>
#include <stdlib.h>
#include <ctype.h>

#if defined(__WIN__)

#ifndef _WINSOCKAPI_
#include <winsock.h>
#endif
#include <windows.h>
#include <initguid.h>
#include <errno.h>
#include <signal.h>
#include <tchar.h>

#else

#include <errno.h>
#include <signal.h>
#include <netdb.h>
#include <unistd.h>
#include <string.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <sys/socket.h>

#endif

#include <yaz/yaz-util.h>
#include <yaz/backend.h>
#include <yaz/ill.h>

Z_GenericRecord *dummy_grs_record (int num, ODR o);
char *dummy_marc_record (int num, ODR odr);
char *dummy_xml_record (int num, ODR odr);

int ztest_search (void *handle, bend_search_rr *rr);
int ztest_sort (void *handle, bend_sort_rr *rr);
int ztest_present (void *handle, bend_present_rr *rr);
//int ztest_esrequest (void *handle, bend_esrequest_rr *rr);
int ztest_delete (void *handle, bend_delete_rr *rr);

/* définitions pour fonction HTTP GET */
#define PACKET_SIZE  1024

int to_server_socket = -1;
char server_name[100]; // nom du host du serveur
int port;
char request_path[PACKET_SIZE];
char database[100];
char bulk[4096];
char id_set[4096];
int http_err;
char http_err_string[255];
char http_content[4096];

int n_results;
int cur_results;
char *ids[100];

/* Fonction d'encodage URL d'une chaine */
char *url_encode(const char *s) {
    const char *hex = "0123456789abcdef";
    size_t len = strlen(s);
    char *out = malloc(len * 3 + 1); // Alloue suffisamment de mémoire pour la chaîne encodée
    size_t o = 0;

    for (size_t i = 0; i < len; i++) {
        if (isalnum(s[i]) || s[i] == '-' || s[i] == '_' || s[i] == '.' || s[i] == '~') {
            out[o++] = s[i]; // Caractères alphanumériques, "-" "_" "." "~" ne sont pas encodés
        } else {
            out[o++] = '%'; // Encodage de caractères spéciaux
            out[o++] = hex[(unsigned char)s[i] >> 4];
            out[o++] = hex[(unsigned char)s[i] & 15];
        }
    }

    out[o] = '\0'; // Ajout de la terminaison de chaîne
    return out;
}

/* Fonction de récupération du contenu renvoyé par le serveur */
int get_http_content() {
	char *pos;
	char *pos1;
	
	//Recherche du 1er @ (numéro d'erreur)
	pos = strstr(bulk,"@");
	if (pos==NULL) return 1;
	*pos = 0x00;
	
	http_err = atoi(bulk);
	
	//Recherche de 2ème @ (chaine d'erreur)
	pos++;
	pos1 = strstr(pos,"@");
	if (pos1==NULL) return 1;
	*pos1 = 0x00;
	strcpy(http_err_string,pos);
	
	//Récupération du contenu
	pos1++;
	strcpy(http_content,pos1);
	return 0;
}

#if defined(__WIN__)

void bcopy( void * source, void * destination, int size )
{
   int i;
   char * src = ( char * ) source;
   char * dst = ( char * ) destination;

   for( i=0; i<size; i++ )
      dst[i] = src[i];
}

void bzero( void * destination, int size )
{
   unsigned int i;	
   char * dst = ( char * ) destination;

   for( i=0; i<size; i++ )
      dst[i] = 0x00;
}

int readn(int fd, char *ptr, int n){
int nl, nr;

        nl = n;
        while ( nl > 0 ) {
                nr = recv(fd,ptr,nl,0);
                if (nr < 0 )
                        return nr;     /*error*/
                else
                        if ( nr == 0 )
                                break;
                nl -= nr;
                ptr += nr;
        }
        *ptr=0x00;
        return (n-nl);
}

int appli (char *query) {
	char buffer[PACKET_SIZE+1];
	char line[PACKET_SIZE+2];
	int rc;
	int i = 0;

 	sprintf(buffer,"");
	sprintf(bulk,"");
  	
  	sprintf(line,"GET %s?%s\r\n",request_path,query);

  	yaz_log(LOG_LOG,"http_get : %s",line);
 
        send(to_server_socket,line,strlen(line)+1,0);

        do {
                rc = readn(to_server_socket,buffer,PACKET_SIZE);
    			if (rc<0) return 1;
    			strcat(bulk,buffer);
        		} while ( rc != 0 ) ;
        return 0;
}

int http_get(char *query)
{

struct sockaddr_in serverSockAddr;    /* addresse de la socket */
struct hostent *serverHostEnt;        /* description du host serveur */
long hostAddr;                       /* addr du serveur */

     /* initialise a zero serverSockAddr */
  bzero(&serverSockAddr,sizeof(serverSockAddr));
     /* converti l'adresse ip  en entier long */
  hostAddr = inet_addr(server_name);
  if ( (long)hostAddr != (long)-1)
      bcopy(&hostAddr,&serverSockAddr.sin_addr,sizeof(hostAddr));
  else     /* si on a donne un nom  */
  {
      serverHostEnt = gethostbyname(server_name);
      if (serverHostEnt == NULL)
      {
         return 1;
      }
      bcopy(serverHostEnt->h_addr,
            &serverSockAddr.sin_addr,serverHostEnt->h_length);
  }
  serverSockAddr.sin_port = htons(port);         /* host to network port  */
  serverSockAddr.sin_family = AF_INET;            /* AF_*** : INET=internet */

  /* creation de la socket */
  if ( (to_server_socket = socket(AF_INET,SOCK_STREAM,0)) < 0)
  {
      return 1;
  }
  /* requete de connexion */
  if(connect(to_server_socket,(struct sockaddr *)&serverSockAddr,
             sizeof(serverSockAddr))<0)
  {
      return 1;
  }

  if (appli(query)) return 1;

  /* fermeture de la connection */
  shutdown(to_server_socket,2);
  closesocket(to_server_socket);
  
  return get_http_content();
}

#else

int readn(int fd, char *ptr, int n) {
  int nl, nr;
  
  nl = n;
  while ( nl > 0 ) {
    nr = read(fd,ptr,nl);
    if (nr < 0 )
      return nr;     /*error*/
    else
      if ( nr == 0 )
	break;
    nl -= nr;
    ptr += nr;
  }
  *ptr = 0x00;
  return (n-nl);
}

int requete (char *query) {
  char buffer[PACKET_SIZE+1];
  char line[PACKET_SIZE+2];
  int rc;
   
  sprintf(bulk,"");
  sprintf(buffer,"");
  
  sprintf(line,"GET %s?%s\r\n",request_path,query);

  yaz_log(YLOG_LOG,"http_get : %s",line);

  send(to_server_socket,line,strlen(line)+1,0);
  do {
    rc = readn(to_server_socket,buffer,PACKET_SIZE);
    if (rc<0) return 1;
    strcat(bulk,buffer);
  } while ( rc != 0 ) ;
  return 0;
}

int http_get(char *query)
{
  struct sockaddr_in serverSockAddr;
  struct hostent *serverHostEnt;
  unsigned long hostAddr;

  /* initialise a zero serverSockAddr */
  bzero((void *)&serverSockAddr,sizeof(serverSockAddr));
  /* converti l'adresse ip en entier long */
  hostAddr = inet_addr(server_name);
  if ( (long)hostAddr != (long)-1)
    bcopy((void *)&hostAddr,(void *)&serverSockAddr.sin_addr,sizeof(hostAddr));
  else                /* si on a donné un nom  */
    {
      serverHostEnt = gethostbyname(server_name);
      if (serverHostEnt == NULL) {
	    return 1;
      }
      bcopy((void *)serverHostEnt->h_addr_list[0],(void *)&serverSockAddr.sin_addr,serverHostEnt->h_length);
    }
  serverSockAddr.sin_port = htons(port);   /* host to network port  */
  serverSockAddr.sin_family = AF_INET;     /* AF_*** : INET=internet */
  
  /* creation de la socket */
  if ( (to_server_socket = socket(AF_INET,SOCK_STREAM,0)) < 0)
    {
      return 1;
    }
  /* requete de connexion */
  if(connect(to_server_socket,(struct sockaddr *)&serverSockAddr,sizeof(serverSockAddr))<0)
    {
      return 1;
    }
  
  if (requete(query)) return 1;
  
  /* fermeture de la connection */
  shutdown(to_server_socket,2);
  close(to_server_socket);
  return get_http_content();
}

#endif

int  get_query(char *result, Z_RPNStructure *query_s, char *err_string, int level) {
  /*Analyse de la requete*/
  char result1[500];
  char result2[500];
  char ope[8];
  char escaped_buf[2048];
  int err;
  int use;

  err=0;
  sprintf(err_string,"");

  if (query_s->which==Z_RPNStructure_complex) {
    switch (query_s->u.complex->roperator->which) {
    case Z_Operator_and:
      sprintf(ope,"and");
      break;
    case Z_Operator_or:
      sprintf(ope,"or");
      break;
    case  Z_Operator_and_not:
      sprintf(ope,"and not");
      break;
    default:
      sprintf(ope,"");
      err=110;
      return err;
      break;
    }
    err=get_query(result1,query_s->u.complex->s1,err_string, level+1);
    if (err) return err;
    err=get_query(result2,query_s->u.complex->s2,err_string, level+1);
    if (err) return err;

    sprintf(result,"%s arg%i!1(%s) arg%i!2(%s)",ope,level,result1,level,result2);
  } else {
    
    //Nombre d'attributs pour le terme > 1 ?
    if (query_s->u.simple->u.attributesPlusTerm->attributes->num_attributes!=2) {
      err=123;
      return err;
    }
    //Type d'attribut <> 1 ?
    if (*query_s->u.simple->u.attributesPlusTerm->attributes->attributes[1]->attributeType!=1) {
      err=113;
      return err;
    }
    //Attribut autorisé ?
    use = *query_s->u.simple->u.attributesPlusTerm->attributes->attributes[1]->value.numeric;
    /*
    if ((use!=7)&&(use!=1003)&&(use!=4)) {
      err=114;
      sprintf(err_string,"1=%i",use);
      return err;
    }*/
    //Type de Terme autorisé ?
    if (query_s->u.simple->u.attributesPlusTerm->term->which!=1) {
      err=229;
      return err;
    } 
    strcpy(escaped_buf,query_s->u.simple->u.attributesPlusTerm->term->u.general->buf);
   	sprintf(result,"%i=%s",use,escaped_buf);
 }
  return 0;
}

int ztest_search (void *handle, bend_search_rr *rr)
{
  int err;
  char query[1024];
  char query_final[2048];
  char err_string[255];
  int i;
  
  if (strcmp(rr->setname,"1")) {
    rr->errcode = 2;
    return 0;
  }
    
   //Si la requete n'est pas de type_1
  if (rr->query->which!=2) {
    rr->errcode = 107;
    return 0;
  }

  err=get_query(query,rr->query->u.type_1->RPNStructure,err_string,0);
  if (!err) {  
    yaz_log(YLOG_LOG,"Translated Query = %s",query);
  } else {
      rr->errcode = err;
      rr->errstring = err_string;
      return 0;
    }

    if (rr->num_bases != 1)
    {
        rr->errcode = 23;
        return 0;
    }
    if (yaz_matchstr (rr->basenames[0], database))
    {
        rr->errcode = 109;
        rr->errstring = rr->basenames[0];
        return 0;
    }
  
  	/* Lancement de la recherche */
  	sprintf(query_final,"query=%s&command=search",url_encode(query));
  	yaz_log(YLOG_LOG,"query:%s",query_final);
  	
  	if (http_get(query_final)) {
  		rr->errcode=2;
  		return 0;	
  	}
  	if (http_err==3) {
  			rr->errcode=114;
  			rr->errstring=http_err_string;
  			return 0;
  	}
  	strcpy(id_set,http_content);
  	
  	n_results=atoi(strtok(id_set,"@"));
  	cur_results=0;
  	i=0;
  	while ((ids[i]=strtok(NULL,"@"))!=NULL) {
  		i++;
  	}
 	
 	rr->hits = n_results;
    return 0;
}

/* result set delete */
int ztest_delete (void *handle, bend_delete_rr *rr)
{
    if (rr->num_setnames == 1 && !strcmp (rr->setnames[0], "1"))
	rr->delete_status = Z_DeleteStatus_success;
    else
        rr->delete_status = Z_DeleteStatus_resultSetDidNotExist;
    return 0;
}

/* Our sort handler really doesn't sort... */
int ztest_sort (void *handle, bend_sort_rr *rr)
{
    rr->errcode = 0;
    rr->sort_status = Z_SortStatus_success;
    return 0;
}


/* present request handler */
int ztest_present (void *handle, bend_present_rr *rr)
{
    return 0;
}

/* retrieval of a single record (present, and piggy back search) */
int ztest_fetch(void *handle, bend_fetch_rr *r)
{
	char query[100];
	
    r->errstring = 0;
    r->last_in_set = 0;
    r->basename = database;
    r->output_format = r->request_format;  
    
    
    if (cur_results>=n_results) {
      r->errcode=13;
      return 0;
    } else {
    	sprintf(query,"query=%s&command=get_notice",ids[cur_results]);
    	cur_results++;
    	if (cur_results==n_results) 
    		r->last_in_set=1;	
    		
      	if (http_get(query)) {	
			r->errcode=2;
			return 0;
      	}
    }
    yaz_log(YLOG_LOG,"sending notice for notice_id %s",ids[cur_results-1]);
    r->len = strlen(http_content);
    r->record = http_content;

    //r->output_format = odr_getoidbystr(r->output_format, "1.2.840.10003.3.1");
    oid_dotstring_to_oid("1.2.840.10003.5.1", r->output_format);

    r->errcode = 0;
    return 0;
}

/*
 * no scan allowed
 */

int ztest_scan(void *handle, bend_scan_rr *q)
{
	/*Nothing to do*/
    return 0;
}

static int ztest_explain(void *handle, bend_explain_rr *rr)
{
    if (rr->database && !strcmp(rr->database, "Default"))
    {
	rr->explain_buf = "<explain>\n"
	    "\t<serverInfo>\n"
	    "\t\t<host>localhost</host>\n"
	    "\t\t<port>210</port>\n"
	    "\t</serverInfo>\n"
	    "</explain>\n";
    }
    return 0;
}

int read_conf_file() {
	statserv_options_block *options;
	FILE *configfile;
	char param[100];
	char value[100];
	char *param1;
	char *value1;
	char line[201];
	int flagparam;
	int err;
	
	err=0;
	
	options = statserv_getcontrol();	
	
	  //Lecture des paramètres
    configfile = fopen(options->configname,"r");
    if (configfile==NULL) {
    	return 1;
    }
    while (!feof(configfile)) {
    	fgets(line,4096,configfile);
    	if (line[strlen(line)-1]==13) line[strlen(line)-1]=0x00;
    	if (line[strlen(line)-1]==10) line[strlen(line)-1]=0x00;
    	param1 = strtok(line,"=");
    	if (param1!=NULL) {
    		strcpy(param,param1);
    		value1=strtok(NULL,"=");
    		if (value1==NULL) {
    			if (param[0]!=35) {
  					return 1;
    			}
    		} else {
    			strcpy(value,value1);
    			flagparam=0;
    			yaz_log(YLOG_LOG,"param %s %s",param,value);
    			//webpmb_host
    			if (!strcmp(param,"webpmb_host")) {
    				strcpy(server_name,value);
    				flagparam=1;
    			}
    			//webpmb_port
    			if (!strcmp(param,"webpmb_port")) {
    				port=atoi(value);
    				flagparam=1;
    			}
    			//webpmb_script
    			if (!strcmp(param,"webpmb_script")) {
    				strcpy(request_path,value);
    				flagparam=1;
    			}
    			//z3950_database
    			if (!strcmp(param,"z3950_database")) {
    				strcpy(database,value);
    				flagparam=1;
    			}
    			if (flagparam==0) {
    				return 1;
    			}
    		}
    	}
    }
    fclose(configfile);
    return 0;
}

bend_initresult *bend_init(bend_initrequest *q)
{
    bend_initresult *r = (bend_initresult *)
        odr_malloc (q->stream, sizeof(*r));
    int *counter = (int *) xmalloc (sizeof(int));

    *counter = 0;
    r->errcode = 0;
    r->errstring = 0;
    r->handle = counter;         /* user handle, in this case a simple int */
    q->bend_sort = ztest_sort;              /* register sort handler */
    q->bend_search = ztest_search;          /* register search handler */
    q->bend_present = ztest_present;        /* register present handle */
    //q->bend_esrequest = ztest_esrequest;
    q->bend_delete = ztest_delete;
    q->bend_fetch = ztest_fetch;
    q->bend_scan = ztest_scan;
    q->bend_explain = ztest_explain;

    // q->query_charset = "UTF-8";
    // q->records_in_same_charset = 1;
    
    if (read_conf_file()) {
    	yaz_log(YLOG_LOG,"Can't handle configuration file");
    	r->errcode = 2;
    }
     return r;
}

void bend_close(void *handle)
{
    xfree (handle);              /* release our user-defined handle */
    return;
}

int main(int argc, char **argv)
{
    return statserv_main(argc, argv, bend_init, bend_close);
}
