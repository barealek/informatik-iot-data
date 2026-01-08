FROM golang:1.25-alpine AS build

WORKDIR /app

COPY go.* .
RUN go mod download -x

COPY . .
RUN CGO_ENABLED=0 go build -o /bin/app .

FROM alpine:latest
COPY --from=build /bin/app /app

CMD [ "/app" ]
