
# Just for testing

## Bring up the application containers
```
kubectl apply -f <(istioctl kube-inject -f ./istio.yaml)
```

## Define the ingress gateway for the application

```
kubectl apply -f istio-gateway.yaml
```

## Access the istio service using curl

```
curl -I http://${GATEWAY_URL}/istio1
```

## Check the result in Browser

```
kubectl port-forward -n istio-system $(kubectl get pod -n istio-system -l app=jaeger -o jsonpath='{.items[0].metadata.name}') 16686:16686 &

http://localhost:16686
```



