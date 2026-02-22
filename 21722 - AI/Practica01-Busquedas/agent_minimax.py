from practica import joc
from practica.estat import Estat
import time

# Agente que implementa Minimax con poda alpha-beta
class Viatger(joc.Viatger):
    def __init__(self, *args, **kwargs):
        super(Viatger, self).__init__(*args, **kwargs)
        self.nodes_totals = 0
        self.temps_global = 0

    def cerca(self, estat: Estat, alpha, beta, torn_max=True, iter = 0, max_prof=3) -> tuple[Estat, int]:
        # Métricas
        if iter == 0:
            self.nodes_visitats = 0
            self.temps_inicial = time.time()
        self.nodes_visitats += 1        
        
        # Si ya se llegó a la meta o se alcanzó la profundidad máxima
        if estat.es_meta() or iter > max_prof:            
            # se devuelve la hoja del árbol + estado + heurística
            return estat, estat.heuristica_minimax(self.nom)

        # Lista de puntuaciones de los hijos y generación de hijos
        puntuacio_fills = []
        fills = estat.genera_fills(3)
        
        # Si no se ha generado ninguno (no hay movimiento legal alguno)
        # o es "callejón sin salida"
        if not fills:
            # devolver hoja del árbol
            return estat, estat.heuristica_minimax(self.nom)
        
        # Cada hijo llama recursivamente, cambiando el turno entre MIN y MAX
        for fill in fills:
            punt_fill = self.cerca(fill, alpha, beta, not torn_max, iter + 1)

            if torn_max:
                alpha = max(alpha, punt_fill[1])
            else:
                beta = min(beta, punt_fill[1])

            # Almacenar puntuación del hijo
            puntuacio_fills.append(punt_fill)

            # Poda: se deja de explorar hijos en el nivel
            # de profundidad actual
            if alpha >= beta:
                break

        # Elegir el hijo con mejor puntuación (MAX: más alto, MIN: más bajo)
        idx_millor_f = Viatger.arg_max(puntuacio_fills, not torn_max)
        return puntuacio_fills[idx_millor_f]

    @staticmethod
    def arg_max(estats, reverse = False):
        major_idx = 0
        major_puntuacio = estats[0][1]

        if reverse:
            major_puntuacio *= -1

        for i, estat in enumerate(estats):
            puntuacio_estat = estat[1]

            if reverse:
                puntuacio_estat *= -1

            if puntuacio_estat > major_puntuacio:
                major_idx = i
                major_puntuacio = puntuacio_estat

        return major_idx

    def actua(self, percepcio: dict):
        # Si aún no existe un plan, construye un estado inicial
        # a partir de la percepción ("foto") del entorno           
        estat_inicial = Estat(
                parets = set(percepcio["PARETS"]),
                desti = tuple(percepcio["DESTI"]),
                pos_agents = dict(percepcio["AGENTS"]),
                mida = tuple(percepcio["MIDA"]),
                torn = self.nom
            )
        
        # Primera llamada a Minimax
        resultado = self.cerca(estat_inicial, alpha=-float('inf'), beta=float('inf'))

        temps_total = time.time() - self.temps_inicial
        self.temps_global += temps_total
        self.nodes_totals += self.nodes_visitats
        
        # Si se ha devuelto un plan (estado, heurística), es decir, un plan con un camino, se hace
        # el siguiente paso
        if isinstance(resultado, tuple) and resultado[0].cami is not None and len(resultado[0].cami) > 0:
            solucio, _ = resultado
            accio, direccio = solucio.cami[0]
            return accio, direccio
        else:
            #print(f"Temps total GLOBAL: {self.temps_global}")
            #print(f"Nodes visitats GLOBALS: {self.nodes_totals}")
            return "ESPERAR", None