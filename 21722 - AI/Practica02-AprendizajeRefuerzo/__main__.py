from practicarl.AgentMonteCarlo import AgentMonteCarlo
from practicarl.AgentSARSA import AgentSARSA
from practicarl.AgentQLearning import AgentQLearning
from practicarl.AgentGenetico import AgentGenetico
import gymnasium as gym
import numpy as np
import time

def main():
    # Creamos el entorno
    env =gym.make("FrozenLake-v1",is_slippery= True)

    n_agente = 1    # 1: Monte Carlo, 3: SARSA, 4: Q-Learning, 5: Alg.Genético

    if n_agente != 5: train_episodes = [1000, 3000, 5000, 7000, 10000, 15000, 20000, 25000, 30000]    # episodios de entrenamiento
    else:             train_episodes = [50, 100, 200]
    n_politicas = 25        # numero de politicas generadas dado un número de episodios
    eval_episodes = 25     # episodios con los que se evaluarán cada política aprendidas
    
    # Valores posibles
    gammas = [0.7, 0.85, 0.99]
    alphas = [0.1, 0.3, 0.5]
    epsilons = [0.1, 0.2, 0.3]
    tams_poblacion = [20, 30, 50]
    ns_episodios = [10, 20, 30, 50]

    # Diccionario de parámetros por agente
    param_agentes = {
        1: [gammas, [1], [1]],
        2: [gammas, [1], [1]],
        3: [gammas, alphas, epsilons],
        4: [gammas, alphas, epsilons],
        5: [tams_poblacion, ns_episodios, [1]],
    }

    etiquetas_agentes = {
        1: ["gamma", " ", " "],
        2: ["gamma", " ", " "],
        3: ["gamma", "alpha", "epsilon"],
        4: ["gamma", "alpha", "epsilon"],
        5: ["tam_poblacion", "n_episodios", " "],
    }

    # Selección
    parametros = param_agentes.get(n_agente, [[1], [1], [1]])
    etiquetas = etiquetas_agentes.get(n_agente, [" ", " ", " "])

    evaluar_entrenamientos(env, n_agente, train_episodes, n_politicas, eval_episodes, parametros, etiquetas)

def evaluar_entrenamientos(env, n_agente, train_episodes, n_politicas, eval_episodes, parametros, etiquetas):

    for p1 in parametros[0]:
        if p1 != 1: print(etiquetas[0] + f": {p1}")
        for p2 in parametros[1]:
            if p2 != 1: print("\t" + etiquetas[1] + f": {p2}")
            for p3 in parametros[2]:
                if p3 != 1: print("\t\t" + etiquetas[2] + f": {p3}")
                for episodes in train_episodes:
                    tiempos = []
                    exitos = []
                    pasos = []

                    for i in range(n_politicas):
                        # Instancia  uno de los agentes disponibles
                        match(n_agente):
                            case 1: agente = AgentMonteCarlo(gamma = p1, n_episodios = episodes)
                            case 3: agente = AgentSARSA(gamma = p1, alpha = p2, epsilon = p3, n_episodios = episodes)
                            case 4: agente = AgentQLearning(gamma = p1, alpha = p2, epsilon = p3, n_episodios = episodes)
                            case 5: agente = AgentGenetico(tam_poblacion=p1, n_generaciones=episodes, n_episodios=p2)
                        
                        # print("Calculando política...")
                        start = time.time()
                        politica = agente.train(env)
                        tiempo = time.time() - start
                        # print("Cálculo completado.\n")

                        # mostrar_politica(politica)
                        # mostrar_episodio(agente)

                        tasa_exito, media_pasos = evaluar_politica(env, eval_episodes, politica)
                        tiempos.append(tiempo)
                        exitos.append(tasa_exito)
                        pasos.append(media_pasos)

                        #mostrar_metricas(i, episodes, tasa_exito, media_pasos, tiempo)

                    tiempo_medio = np.mean(tiempos)
                    exito_medio = np.mean(exitos)
                    pasos_media = np.mean(pasos)

                    print(f"\t\t\tResumen: "
                        f"Episodios: {episodes}, "
                        f"Tiempo: {tiempo_medio:.2f}, "
                        f"Exito: {exito_medio:.2f}, "
                        f"Pasos: {pasos_media:.2f}")
                    # print("\n")

def evaluar_politica(env, episodios, politica):
    exitos = 0                  # Recuento exitos
    longitudes = []             # Pasos por cada episodio

    for _ in range(episodios):
        estado, _ = env.reset()
        acabado = False
        # terminado = False
        pasos = 0
        while not acabado:
            accion = politica[estado]
            estado, recompensa, terminado, truncado, _ = env.step(accion)
            pasos += 1
            if recompensa == 1: exitos += 1
            if terminado: longitudes.append(pasos)
            acabado = terminado or truncado

    tasa_exito = exitos / episodios
    longitud_media = np.mean(longitudes) if longitudes else float("inf")

    return tasa_exito, longitud_media

def mostrar_metricas(i, episodios, exito, pasos, tiempo):
    print(f"Iteración: {i}, "
          f"Episodios: {episodios}, "
          f"Tiempo: {tiempo:.2f}s, "
          f"Exito: {exito:.2f}, "
          f"Pasos: {pasos:.2f}, ")
    
def mostrar_politica(politica):
    simbolos = {
        0: '<',  # izquierda
        1: 'v',  # abajo
        2: '>',  # derecha
        3: '^'   # arriba
    }

    print("Política aprendida:")
    for i in range(4):
        fila = ""
        for j in range(4):
            estado = i * 4 + j
            accion = politica[estado]
            fila += simbolos[accion] + " "
        print(fila)
    print()

def mostrar_episodio(agente):
    # Visualización del agente que ha aprendido una política
    env_visual = gym.make("FrozenLake-v1", is_slippery = True, render_mode="human")

    # Empezar un nuevo episodio
    estado, _ = env_visual.reset()
    acabado = False

    while not acabado:
        accion = agente.actua(estado)    # elegir la siguiente acción siguiendo política
        estado, _, fin, truncar, _ = env_visual.step(accion)
        acabado = fin or truncar         # finalizar si se cae el agente o llega la meta
    
    env_visual.close()

if __name__ == '__main__':
    main()