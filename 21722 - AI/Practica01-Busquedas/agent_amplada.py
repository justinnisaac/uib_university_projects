from practica import joc
from practica.estat import Estat
import time

# Agente que implementa búsqueda por amplitud
class Viatger(joc.Viatger):
    def __init__(self, *args, **kwargs):
        super(Viatger, self).__init__(*args, **kwargs)
        self.__frontera = None # cola de estados pendientes por explorar
        self.__tancats = None # conjunto de estados ya procesados
        self.__cami_exit = None # guarda el plan (lista de (acción, dirección)) cuando se halle la meta

    def cerca(self, estat_inicial: Estat) -> bool:
        # Control
        nodes_visitats = 0
        temps_inicial = time.time()

        # Frontera y conjunto de cerrados vacío
        self.__frontera = []
        self.__tancats = set()
        self.__frontera.append(estat_inicial)
        
        # Mientras haya estados pendientes
        while self.__frontera:
            # Saca el primero de la cola (B-F Search -> FIFO)
            estat_actual = self.__frontera.pop(0)
            #print("Pop: {}".format(estat_actual))

            nodes_visitats += 1

            # Si el estado ya fue procesado, se salta
            if estat_actual in self.__tancats:
                #print("Tancat")
                continue

            # Si se ha encontrado la meta, se copia el
            # camino acumulado en cami
            if estat_actual.es_meta():
                #print("És meta")
                self.__cami_exit = estat_actual.cami
                
                temps_total = time.time() - temps_inicial
                print(f"Temps total: {temps_total}")
                print(f"Nodes visitats: {nodes_visitats}")

                return True

            # Se expande el estado actual con una lista
            # de sucesores legales tras aplicar cada acción
            # posible. Añadidos al final de la cola.
            for f in estat_actual.genera_fills(1):
                #print("Fill: {}".format(f))
                self.__frontera.append(f)

            # Marcar el estado que se acaba de evaluar como cerrado
            self.__tancats.add(estat_actual)
        
        # No hay solución (o camino legal)
        return False

    def actua(self, percepcio: dict):
        # Si aún no existe un plan, construye un estado inicial
        # a partir de la percepción ("foto") del entorno
        if self.__cami_exit is None:
            estat_inicial = Estat(
                parets = set(percepcio["PARETS"]),
                desti = tuple(percepcio["DESTI"]),
                pos_agents = dict(percepcio["AGENTS"]),
                mida = tuple(percepcio["MIDA"]),
                torn = self.nom # agente actual
            )

            #print(f"Posició inicial: {estat_inicial.pos_agents["AGENT_1"]}")

            trobat = self.cerca(estat_inicial)
            if not trobat:
                # No hay solución, no se hace nada
                return "ESPERAR", None

        # Si hay plan, se sigue con el siguiente paso
        if self.__cami_exit:
            accio, direccio = self.__cami_exit.pop(0)
            #print(f"Execució: {accio}, {direccio}")
            return accio, direccio
        else:
            return "ESPERAR", None