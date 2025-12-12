# Microservice Product Import System

This repository contains an evolving microservice-based application designed to handle product data ingestion and persistence. Development is ongoing, and features are added incrementally over time.

## 🧩 Architecture Overview

Currently, the system consists of two microservices:

### 1. Reader Service
- Responsible for ingesting product data from external sources.
- Currently supports reading from CSV files.
- Parses and normalizes product entries before forwarding them to the Writer service.

### 2. Writer Service
- Handles persistence of product data.
- Accepts product payloads via HTTP.
- Saves data into a configured backend (currently MySQL).

## 🔁 Communication
- The Reader and Writer services communicate synchronously via HTTP.
- Future plans include support for asynchronous messaging (e.g., AMQP, Redis streams).

## 🚧 Status
- This project is under active development.
- Configuration-driven design allows flexible extension of data sources and storage backends.

Stay tuned for updates as the system evolves toward a more scalable and event-driven architecture.
