from iaLib import agent

import numpy as np
import random

import gymnasium as gym
from gymnasium.core import Env

class AgentMonteCarlo(agent.Agent):
    """
    Agente que implementa el algoritmo Monte Carlo para aprender
    
    Fucionamiento:
        - Cada episodio empieza en un par (S0, A0) aleatorio
          Esto garantiza que se exploren todos los estados y acciones
        - Se genera un episodio completo siguiento la política actual
        - Se recorre cada episodio hacia atrás, calculando el retorno G
        - Para cada par (s,a), si es su primera visita en el episodio,
          se añade G a Returns(s,a) y se actualiza Q(s,a) como la media 
          de los retornos en Returns(s,a)
        - π(s) se define como la acción en estado s que tiene mayor Q(s,a)
    """

    def __init__(self, gamma, n_episodios, seed = 0):
        super().__init__(long_memoria=0)

        self.gamma = gamma
        self.n_episodios = n_episodios
        self.n_estados = 16
        self.n_acciones = 4

        self.Q = np.zeros((self.n_estados,self.n_acciones), dtype=float)  # Q(s,a) valor de cada acción en cada estado
        self.pi = np.zeros(self.n_estados, dtype=int)   # pi(s) mejor acción en cada estado
                                                        # política inicial arbitraria
        self.Returns = [[[] for _ in range(4)] for _ in range(self.n_estados)]  # Matriz 16x4 de listas inicialmente vacías
                                                                                # Returns(s,a) lista de retornos para cada par (s,a)
        
        np.random.seed(seed)
        random.seed(seed)

    def train(self, env: Env):     
        for _ in range(self.n_episodios):
            # Elegir (S0, A0) aleatorio
            estado_inicial = random.randrange(self.n_estados)
            accion_inicial = random.randrange(self.n_acciones)

            # Resetear el entorno en S0
            env.reset()
            env.unwrapped.s = estado_inicial
            episodio = []

            # Ejecutar acción A0
            estado_nuevo, recompensa, fin, truncar, _ = env.step(accion_inicial)
            episodio.append((estado_inicial, accion_inicial, recompensa))

            estado = estado_nuevo
            terminal = fin or truncar

            # Generar el episodio completo siguiendo la política actual π
            while not terminal:
                accion = self.pi[estado]  # acción según política actual
                estado_nuevo, recompensa, fin, truncar, _ = env.step(accion)
                episodio.append((estado, accion, recompensa))

                estado = estado_nuevo
                terminal = fin or truncar

            # Recorrer el episodio hacia atrás
            G = 0
            visitados = set()

            for t in reversed(range(len(episodio))):
                estado_t, accion_t, recompensa_t = episodio[t]
                G = self.gamma * G + recompensa_t

                if(estado_t, accion_t) not in visitados:
                    visitados.add((estado_t, accion_t))

                    self.Returns[estado_t][accion_t].append(G)

                    self.Q[estado_t][accion_t] = np.mean(self.Returns[estado_t][accion_t])

                    self.pi[estado_t] = np.argmax(self.Q[estado_t]) 
        return self.pi

    def actua(self, estado):
        """
        Devuelve la acción según la política aprendida (greedy)
        """
        return self.pi[estado]